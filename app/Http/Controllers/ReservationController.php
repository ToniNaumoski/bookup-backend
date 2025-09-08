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
     * Display a listing of the user's reservations.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $reservations = Reservation::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'reservations' => $reservations
        ]);
    }

    /**
     * Store a newly created reservation.
     */
    public function store(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Get the business associated with the authenticated user
        $business = Business::where('user_id', $user->id)->first();
        
        // Check if the user has a business
        if (!$business) {
            return response()->json([
                'success' => false,
                'message' => 'No business found for this user'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if reservation already exists
        $existingReservation = Reservation::where('business_id', $business->id)
            ->where('date', $request->date)
            ->where('time', $request->time)
            ->where('status', 'active')
            ->first();

        if ($existingReservation) {
            return response()->json([
                'success' => false,
                'message' => 'Времето е веќе резервирано!'
            ], 422);
        }

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'date' => $request->date,
            'time' => $request->time,
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Резервацијата е успешно направена!',
            'reservation' => $reservation
        ]);
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        // Check if the reservation belongs to the authenticated user
        if ($reservation->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Резервацијата е откажана!'
        ]);
    }
}