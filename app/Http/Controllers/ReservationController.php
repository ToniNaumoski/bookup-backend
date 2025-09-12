<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    /**
     * Display a listing of the user's reservations or business reservations.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user has a business
        $business = Business::where('user_id', $user->id)->first();

        if ($business) {
            // Business owner - show reservations made FOR their business
            $reservations = Reservation::where('business_id', $business->id)
                ->with(['user']) // Include user details
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->get();
        } else {
            // Regular user - show their own reservations (all statuses)
            $reservations = Reservation::where('user_id', $user->id)
                ->with(['business']) // Include business details
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->get();
        }

        return response()->json([
            'success' => true,
            'reservations' => $reservations,
            'is_business_owner' => $business ? true : false
        ]);
    }

    /**
     * Store a newly created reservation.
     */
    public function store(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the business that the user wants to make a reservation for
        $business = Business::findOrFail($request->business_id);

        // Check if the business is approved
        if ($business->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'This business is not available for reservations'
            ], 422);
        }

        // Check if reservation already exists by another user (not the business owner)
        $existingReservation = Reservation::where('business_id', $business->id)
            ->where('date', $request->date)
            ->where('time', $request->time)
            ->whereIn('status', ['active', 'confirmed'])
            ->where('user_id', '!=', $business->user_id) // Exclude reservations created by business owner
            ->first();

        if ($existingReservation) {
            return response()->json([
                'success' => false,
                'message' => 'Времето е веќе резервирано!'
            ], 422);
        }

        // Check if the requested time is within business hours
        $isWithinBusinessHours = $this->isTimeWithinBusinessHours($request->date, $request->time, $business);

        if (!$isWithinBusinessHours) {
            return response()->json([
                'success' => false,
                'message' => 'Времето треба да биде во работните сати на бизнисот!'
            ], 422);
        }

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'date' => $request->date,
            'time' => $request->time,
            'status' => 'pending' // Changed from 'active' to 'pending' for approval workflow
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Резервацијата е успешно направена! Бизнисот ќе ја разгледа вашата барање.',
            'reservation' => $reservation
        ]);
    }

    /**
     * Confirm a reservation (for business owners).
     */
    public function confirm(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        // Check if the authenticated user owns the business for this reservation
        $business = Business::where('user_id', Auth::id())->first();

        if (!$business || $reservation->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You can only manage reservations for your own business'
            ], 403);
        }

        // Only allow confirming pending reservations
        if ($reservation->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending reservations can be confirmed'
            ], 422);
        }

        $reservation->status = 'confirmed';
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Резервацијата е потврдена!'
        ]);
    }

    /**
     * Cancel a reservation (for both business owners and regular users).
     */
    public function cancel(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        $user = Auth::user();

        // Check if the authenticated user owns the business for this reservation
        $business = Business::where('user_id', $user->id)->first();

        if ($business && $reservation->business_id === $business->id) {
            // Business owner cancelling a reservation for their business
            // Only allow canceling pending or confirmed reservations
            if (!in_array($reservation->status, ['pending', 'confirmed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This reservation cannot be cancelled'
                ], 422);
            }

            $reservation->status = 'cancelled';
            $reservation->save();

            return response()->json([
                'success' => true,
                'message' => 'Резервацијата е откажана од бизнисот!'
            ]);
        } elseif ($reservation->user_id === $user->id) {
            // Regular user cancelling their own reservation
            // Only allow canceling pending or confirmed reservations
            if (!in_array($reservation->status, ['pending', 'confirmed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Оваа резервација не може да се откаже'
                ], 422);
            }

            $reservation->status = 'cancelled';
            $reservation->save();

            return response()->json([
                'success' => true,
                'message' => 'Вашата резервација е успешно откажана!'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Немате дозвола да ја откажете оваа резервација'
            ], 403);
        }
    }

    /**
     * Delete a reservation permanently.
     */
    public function destroy(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        // Check if the reservation belongs to the authenticated user
        if ($reservation->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Allow deletion of both active and cancelled reservations
        $reservation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Резервацијата е трајно избришана!'
        ]);
    }

    /**
     * Check if the requested time is within business hours
     */
    private function isTimeWithinBusinessHours($date, $time, $business)
    {
        // Get day of week from date (0 = Sunday, 1 = Monday, etc.)
        $dayOfWeek = date('w', strtotime($date));

        // Map day numbers to day names
        $dayNames = [
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday'
        ];

        $dayName = $dayNames[$dayOfWeek];

        // Get business working hours
        $workingHours = $business->working_hours;

        // If working hours is a JSON string, decode it
        if (is_string($workingHours)) {
            $workingHours = json_decode($workingHours, true);
        }

        // If no working hours are set, allow the reservation
        if (!$workingHours || !is_array($workingHours)) {
            return true;
        }

        // Check if the day has working hours configuration
        if (!isset($workingHours[$dayName])) {
            // If day is not configured, assume it's closed
            return false;
        }

        $dayHours = $workingHours[$dayName];

        // Check if business is closed that day
        if (isset($dayHours['closed']) && $dayHours['closed']) {
            return false;
        }

        // Check if open and close times are set
        if (!isset($dayHours['open']) || !isset($dayHours['close'])) {
            return false;
        }

        // Check if times are empty strings
        if (empty($dayHours['open']) || empty($dayHours['close'])) {
            return false;
        }

        $requestedTime = strtotime($time);
        $openTime = strtotime($dayHours['open']);
        $closeTime = strtotime($dayHours['close']);

        // Check if requested time is within business hours
        return ($requestedTime >= $openTime && $requestedTime <= $closeTime);
    }
}
