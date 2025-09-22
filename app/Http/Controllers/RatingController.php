<?php

namespace App\Http\Controllers;

use App\Models\UserBusinessRating;
use App\Models\BusinessUserRating;
use App\Models\Reservation;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RatingController extends Controller
{
    /**
     * Submit a rating from user to business
     */
    public function submitUserRating(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'required|exists:reservations,id',
            'stars' => 'required|integer|min:1|max:5',
            'message' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $reservation = Reservation::findOrFail($request->reservation_id);

        // Check if user owns this reservation
        if ($reservation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if reservation is confirmed
        if ($reservation->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Можете да оцените само потврдени резервации'
            ], 422);
        }

        // Temporarily allow all confirmed reservations for testing
        // Allow rating for confirmed reservations from today or past dates
        // $reservationDate = Carbon::createFromFormat('Y-m-d', $reservation->date);
        // $today = Carbon::today();

        // if ($reservationDate->lte($today)) {
            // Allow rating for today or past reservations
        // } else {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Можете да оцените само завршени резервации'
        //     ], 422);
        // }

        // Check if rating already exists
        $existingRating = UserBusinessRating::where('user_id', $user->id)
            ->where('reservation_id', $reservation->id)
            ->first();

        if ($existingRating) {
            return response()->json([
                'success' => false,
                'message' => 'Веќе сте оцениле оваа резервација'
            ], 422);
        }

        // Create rating
        $rating = UserBusinessRating::create([
            'user_id' => $user->id,
            'business_id' => $reservation->business_id,
            'reservation_id' => $reservation->id,
            'stars' => $request->stars,
            'message' => $request->message
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Оценката е успешно испратена!',
            'rating' => $rating->load(['user', 'business'])
        ]);
    }

    /**
     * Submit a rating from business to user
     */
    public function submitBusinessRating(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'required|exists:reservations,id',
            'stars' => 'required|integer|min:1|max:5',
            'message' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $business = Business::where('user_id', $user->id)->first();

        if (!$business) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You are not a business owner'
            ], 403);
        }

        $reservation = Reservation::findOrFail($request->reservation_id);

        // Check if reservation belongs to this business
        if ($reservation->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if reservation is confirmed
        if ($reservation->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Можете да оцените само потврдени резервации'
            ], 422);
        }

        // Temporarily allow all confirmed reservations for testing
        // Allow rating for confirmed reservations from today or past dates
        // $reservationDate = Carbon::createFromFormat('Y-m-d', $reservation->date)->startOfDay();
        // $today = Carbon::now()->startOfDay();

        // \Log::info('Rating validation - Reservation date: ' . $reservation->date . ', Parsed: ' . $reservationDate->format('Y-m-d') . ', Today: ' . $today->format('Y-m-d'));
        // \Log::info('Date comparison - reservationDate <= today: ' . ($reservationDate->lte($today) ? 'true' : 'false'));

        // if ($reservationDate->lte($today)) {
            // Allow rating for today or past reservations
            \Log::info('Rating temporarily allowed for all confirmed reservations');
        // } else {
        //     \Log::info('Rating blocked - reservation date is in future');
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Можете да оцените само завршени резервации'
        //     ], 422);
        // }

        // Check if rating already exists
        $existingRating = BusinessUserRating::where('business_id', $business->id)
            ->where('reservation_id', $reservation->id)
            ->first();

        if ($existingRating) {
            return response()->json([
                'success' => false,
                'message' => 'Веќе сте оцениле овој корисник за оваа резервација'
            ], 422);
        }

        // Create rating
        $rating = BusinessUserRating::create([
            'business_id' => $business->id,
            'user_id' => $reservation->user_id,
            'reservation_id' => $reservation->id,
            'stars' => $request->stars,
            'message' => $request->message
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Оценката е успешно испратена!',
            'rating' => $rating->load(['business', 'user'])
        ]);
    }

    /**
     * Get ratings for a business
     */
    public function getBusinessRatings($businessId)
    {
        $business = Business::findOrFail($businessId);

        $ratings = UserBusinessRating::where('business_id', $businessId)
            ->with(['user', 'reservation'])
            ->orderBy('created_at', 'desc')
            ->get();

        $averageRating = $ratings->avg('stars') ?? 0;
        $totalRatings = $ratings->count();

        return response()->json([
            'success' => true,
            'business' => $business,
            'ratings' => $ratings,
            'average_rating' => round($averageRating, 1),
            'total_ratings' => $totalRatings
        ]);
    }

    /**
     * Get ratings for a user
     */
    public function getUserRatings($userId)
    {
        $user = \App\Models\User::findOrFail($userId);

        $ratings = BusinessUserRating::where('user_id', $userId)
            ->with(['business', 'reservation'])
            ->orderBy('created_at', 'desc')
            ->get();

        $averageRating = $ratings->avg('stars') ?? 0;
        $totalRatings = $ratings->count();

        return response()->json([
            'success' => true,
            'user' => $user,
            'ratings' => $ratings,
            'average_rating' => round($averageRating, 1),
            'total_ratings' => $totalRatings
        ]);
    }

    /**
     * Check if user can rate a reservation
     */
    public function canRateReservation($reservationId)
    {
        $user = Auth::user();
        $reservation = Reservation::findOrFail($reservationId);

        $canRate = false;
        $message = '';

        if ($reservation->user_id === $user->id) {
            // User rating business
            if ($reservation->status === 'confirmed') {
                // Temporarily allow all confirmed reservations
                $existingRating = UserBusinessRating::where('user_id', $user->id)
                    ->where('reservation_id', $reservation->id)
                    ->exists();
                if (!$existingRating) {
                    $canRate = true;
                } else {
                    $message = 'Веќе сте оцениле оваа резервација';
                }
            } else {
                $message = 'Резервацијата не е потврдена';
            }
        } elseif ($reservation->business->user_id === $user->id) {
            // Business rating user
            if ($reservation->status === 'confirmed') {
                // Allow rating for confirmed reservations from today or past dates
                $reservationDate = Carbon::createFromFormat('Y-m-d', $reservation->date);
                $today = Carbon::today();

                if ($reservationDate->lte($today)) {
                    $existingRating = BusinessUserRating::where('business_id', $reservation->business_id)
                        ->where('reservation_id', $reservation->id)
                        ->exists();
                    if (!$existingRating) {
                        $canRate = true;
                    } else {
                        $message = 'Веќе сте оцениле овој корисник';
                    }
                } else {
                    $message = 'Date validation temporarily disabled';
                }
            } else {
                $message = 'Резервацијата не е потврдена';
            }
        } else {
            $message = 'Немате дозвола да оцените оваа резервација';
        }

        return response()->json([
            'success' => true,
            'can_rate' => $canRate,
            'message' => $message
        ]);
    }
}
