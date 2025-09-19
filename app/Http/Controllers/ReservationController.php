<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Events\ReservationCreated;
use App\Models\ReservationMessage;
use App\Notifications\ReservationCreated as ReservationCreatedNotification;
use App\Notifications\ReservationStatusChanged;
use App\Notifications\ReservationCancelledByUser;


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
                ->with(['user', 'messages.sender']) // Include user details and messages
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->get();
        } else {
            // Regular user - show their own reservations (all statuses)
            $reservations = Reservation::where('user_id', $user->id)
                ->with(['business', 'messages.sender']) // Include business details and messages
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

        // If user is a business owner creating a slot, require capacity
        if ($userBusiness) {
            $validatorRules['capacity'] = 'required|integer|min:1|max:100';
        }

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

          // Validate business hours for both business owners and regular users
        if (!$this->isTimeWithinBusinessHours($request->date, $request->time, $business)) {
            $dayOfWeek = date('l', strtotime($request->date)); // Get day name (Monday, Tuesday, etc.)
            
            return response()->json([
                'success' => false,
                'message' => "Избраното време не е во работните часови на бизнисот за {$dayOfWeek}. Ве молиме проверете ги работните часови и изберете друго време."
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
                'status' => 'available', // Available slot created by business owner
                'capacity' => $request->capacity
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Слободниот термин е успешно додаден!',
                'reservation' => $reservation
            ]);
        } else {
            // Regular user booking

            // Check if the user has already made 2 reservations today
            // $reservationCount = Reservation::where('user_id', $user->id)
            //     ->where('date', $request->date)
            //     ->count();
  
            // if ($reservationCount >= 2) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Масимален број на резервации се 2 во еден ден.'
            //     ], 422);
            // }
    
    // Check if user already has a reservation for this exact time
    $existingUserReservation = Reservation::where('date', $request->date)
        ->where('time', $request->time)
        ->where('user_id', $user->id)
        ->whereIn('status', ['pending', 'confirmed'])
        ->first();

    if ($existingUserReservation) {
        return response()->json([
            'success' => false,
            'message' => 'Веќе имате резервација за ова време!'
        ], 422);
    }


    // Check if there's an available slot created by business owner
    $availableSlot = Reservation::where('business_id', $business->id)
        ->where('date', $request->date)
        ->where('time', $request->time)
        ->where('status', 'available')
        ->first();

    if ($availableSlot) {
         // Check capacity - count how many people have already booked this slot
         $currentBookings = Reservation::where('business_id', $business->id)
             ->where('date', $request->date)
             ->where('time', $request->time)
             ->whereIn('status', ['pending', 'confirmed'])
             ->count();

         if ($currentBookings >= $availableSlot->capacity) {
             return response()->json([
                 'success' => false,
                 'message' => 'Овој термин е веќе полн. Не може да се резервира повеќе.'
             ], 422);
         }

         // Check if the user has already made 2 reservations today
         // $reservationCount = Reservation::where('user_id', $user->id)
         //     ->where('date', $request->date)
         //     ->count();

         // if ($reservationCount >= 2) {
         //     return response()->json([
         //         'success' => false,
         //         'message' => 'Масимален број на резервации се 2 во еден ден.'
         //     ], 422);
         // }
        // Create a new reservation record for this user (don't modify the available slot)
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'date' => $request->date,
            'time' => $request->time,
            'status' => 'pending'
        ]);

        $reservation = $reservation->load(['user', 'business']);
        event(new ReservationCreated($reservation));

        // Save initial message if provided
        if ($request->message) {
            ReservationMessage::create([
                'reservation_id' => $reservation->id,
                'sender_id' => $user->id,
                'message' => $request->message,
                'sender_type' => 'user'
            ]);
        }

        // Send email notification to business owner
        $businessOwner = $reservation->business->user;
        $businessOwner->notify(new ReservationCreatedNotification($reservation));

        return response()->json([
            'success' => true,
            'message' => 'Резервацијата е успешно направена! Бизнисот ќе ја разгледа вашата барање.',
            'reservation' => $reservation
        ]);
    } else {
        // Create new direct reservation (no pre-slot required)
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'date' => $request->date,
            'time' => $request->time,
            'status' => 'pending'
        ]);

        $reservation = $reservation->load(['user', 'business']);
        event(new ReservationCreated($reservation));

        // Save initial message if provided
        if ($request->message) {
            ReservationMessage::create([
                'reservation_id' => $reservation->id,
                'sender_id' => $user->id,
                'message' => $request->message,
                'sender_type' => 'user'
            ]);
        }

        // Send email notification to business owner
        $businessOwner = $reservation->business->user;
        $businessOwner->notify(new ReservationCreatedNotification($reservation));

        return response()->json([
            'success' => true,
            'message' => 'Резервацијата е успешно направена! Бизнисот ќе ја разгледа вашата барање.',
            'reservation' => $reservation
        ]);
    }
        }


    }

    /**
     * Confirm a reservation (for business owners).
     */
    public function confirm(Request $request, $id)
    {
        $request->validate([
            'message' => 'nullable|string|max:1000'
        ]);

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

        $oldStatus = $reservation->status;
        $reservation->status = 'confirmed';
        $reservation->save();

        // Save message if provided
        if ($request->message) {
            ReservationMessage::create([
                'reservation_id' => $reservation->id,
                'sender_id' => Auth::id(),
                'message' => $request->message,
                'sender_type' => 'business'
            ]);
        }

        // Send email notification to user
        $reservation->user->notify(new ReservationStatusChanged($reservation, $oldStatus, 'confirmed'));

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
            $request->validate([
                'message' => 'nullable|string|max:1000'
            ]);

            // Business owner cancelling a reservation for their business
            // Only allow canceling pending or confirmed reservations
            if (!in_array($reservation->status, ['pending', 'confirmed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This reservation cannot be cancelled'
                ], 422);
            }

            $oldStatus = $reservation->status;
            $reservation->status = 'cancelled';
            $reservation->save();

            // Save message if provided
            if ($request->message) {
                ReservationMessage::create([
                    'reservation_id' => $reservation->id,
                    'sender_id' => Auth::id(),
                    'message' => $request->message,
                    'sender_type' => 'business'
                ]);
            }

            // Send email notification to user
            $reservation->user->notify(new ReservationStatusChanged($reservation, $oldStatus, 'cancelled'));

            return response()->json([
                'success' => true,
                'message' => 'Резервацијата е откажана од бизнисот!'
            ]);
        } elseif ($reservation->user_id === $user->id) {
            $request->validate([
                'message' => 'nullable|string|max:1000'
            ]);

            // Regular user cancelling their own reservation
            // Only allow canceling pending or confirmed reservations
            if (!in_array($reservation->status, ['pending', 'confirmed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Оваа резервација не може да се откаже'
                ], 422);
            }

            $oldStatus = $reservation->status;
            $reservation->status = 'cancelled';
            $reservation->save();

            // Save message if provided
            if ($request->message) {
                ReservationMessage::create([
                    'reservation_id' => $reservation->id,
                    'sender_id' => Auth::id(),
                    'message' => $request->message,
                    'sender_type' => 'user'
                ]);
            }

            // Send email notification to business owner
            $businessOwner = $reservation->business->user;
            $businessOwner->notify(new ReservationCancelledByUser($reservation, 'cancelled'));

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
     * Send a message for a reservation.
     */
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $reservation = Reservation::findOrFail($id);
        $user = Auth::user();

        // Check if user is authorized (either the user who made the reservation or the business owner)
        $isUser = $reservation->user_id === $user->id;
        $isBusinessOwner = $reservation->business->user_id === $user->id;

        if (!$isUser && !$isBusinessOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Create the message
        $message = ReservationMessage::create([
            'reservation_id' => $reservation->id,
            'sender_id' => $user->id,
            'message' => $request->message,
            'sender_type' => $isBusinessOwner ? 'business' : 'user'
        ]);

        // Load sender relationship
        $message->load('sender');

        return response()->json([
            'success' => true,
            'message' => 'Пораката е успешно испратена!',
            'data' => $message
        ]);
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

        // Send email notification to business owner before deleting
        $businessOwner = $reservation->business->user;
        $businessOwner->notify(new ReservationCancelledByUser($reservation, 'deleted'));

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

        $user = Auth::user();

        // Check if user has a business
        $business = Business::where('user_id', $user->id)->first();

        // Get all available slots with business and user information
        $availableSlotsQuery = Reservation::where('status', 'available')
            ->with(['business', 'business.user'])
            ->whereHas('business', function($query) {
                // Only show slots for approved businesses
                $query->where('status', 'approved');
            })
            ->whereRaw('capacity > (SELECT COUNT(*) FROM reservations r2 WHERE r2.business_id = reservations.business_id AND r2.date = reservations.date AND r2.time = reservations.time AND r2.status IN ("pending", "confirmed"))');

        // If user is not a business owner and has 2 or more reservations today, don't show available slots
        // if (!$business) {
        //     $reservationCount = Reservation::where('user_id', $user->id)
        //         ->where('date', $request->date)
        //         ->count();

        //     if ($reservationCount >= 2) {
        //         $availableSlotsQuery->where('id', null); // Return empty result
        //     }
        // }

        $availableSlots = $availableSlotsQuery->orderBy('date', 'asc')
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
