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
            'is_business_owner' => $business ? true : false,
            'business_owner_id' => $business ? $business->user_id : null
        ]);
    }

    /**
     * Store a newly created reservation.
     */
    public function store(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if user is a business owner
        $userBusiness = Business::where('user_id', $user->id)->first();

        $validatorRules = [
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
        ];

        // If user is a business owner, they don't need to send business_id
        // The backend will automatically use their business
        if ($userBusiness) {
            $business = $userBusiness;
        } else {
            // Regular user must specify which business they want to book at
            $validatorRules['business_id'] = 'required|exists:businesses,id';
        }

        $validator = Validator::make($request->all(), $validatorRules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the business for the reservation
        if ($userBusiness) {
            // Business owner creating reservation for their own business
            $business = $userBusiness;
        } else {
            // Regular user booking at a specific business
            $business = Business::findOrFail($request->business_id);
        }

        // Check if the business is approved
        if ($business->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'This business is not available for reservations'
            ], 422);
        }

        // Check if user is a business owner
        $isBusinessOwner = $userBusiness && $userBusiness->id === $business->id;

        if ($isBusinessOwner) {
            // Business owner creating an available time slot
            // Check if slot already exists (created by business owner)
            $existingSlot = Reservation::where('business_id', $business->id)
                ->where('date', $request->date)
                ->where('time', $request->time)
                ->where('user_id', $business->user_id)
                ->first();

            if ($existingSlot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Овој термин е веќе додаден!'
                ], 422);
            }

            // Create available time slot
            $reservation = Reservation::create([
                'user_id' => $user->id,
                'business_id' => $business->id,
                'date' => $request->date,
                'time' => $request->time,
                'status' => 'available' // Available slot created by business owner
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Слободниот термин е успешно додаден!',
                'reservation' => $reservation
            ]);
        } else {
            // Regular user booking
            // Check if there's an available slot for this time
            $availableSlot = Reservation::where('business_id', $business->id)
                ->where('date', $request->date)
                ->where('time', $request->time)
                ->where('status', 'available')
                ->first();

            if (!$availableSlot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Овој термин не е достапен!'
                ], 422);
            }

            // Check if user already has a reservation for this time
            $existingReservation = Reservation::where('business_id', $business->id)
                ->where('date', $request->date)
                ->where('time', $request->time)
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->first();

            if ($existingReservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Веќе имате резервација за ова време!'
                ], 422);
            }

            // Update the available slot to become a pending reservation
            $availableSlot->user_id = $user->id;
            $availableSlot->status = 'pending';
            $availableSlot->save();

            return response()->json([
                'success' => true,
                'message' => 'Резервацијата е успешно направена! Бизнисот ќе ја разгледа вашата барање.',
                'reservation' => $availableSlot
            ]);
        }


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
     * Get all available slots for public viewing (homepage).
     */
    public function available(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);

        // Get all available slots with business and user information
        $availableSlots = Reservation::where('status', 'available')
            ->with(['business', 'business.user'])
            ->whereHas('business', function($query) {
                // Only show slots for approved businesses
                $query->where('status', 'approved');
            })
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'slots' => $availableSlots
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
