<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use Illuminate\Http\Request;

class PublicBusinessController extends Controller
{
    /**
     * Get all approved businesses with pagination
     */
    public function getApprovedBusinesses(Request $request)
    {
        $query = Business::with(['user', 'reservations'])
            ->where('status', 'approved');

        // Always order by created_at for now - rating sorting will be handled in PHP
        $query->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('city') && $request->city) {
            $query->where('city', $request->city);
        }

        if ($request->has('municipality') && $request->municipality) {
            $query->where('municipality', $request->municipality);
        }

        if ($request->has('category') && $request->category) {
            $query->where('main_category', $request->category);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply rating filter
        if ($request->has('min_rating') && $request->min_rating) {
            $minRating = $request->min_rating;
            $query->whereHas('ratings', function($q) use ($minRating) {
                $q->havingRaw('AVG(stars) >= ?', [$minRating]);
            });
        }

        // Paginate results
        $businesses = $query->paginate(12);

        // Add reservation count and ratings to each business
        $businesses->getCollection()->transform(function ($business) {
            $business->reservation_count = $business->reservations()->count();

            // Get ratings data from relationship
            $ratings = $business->ratings;
            $business->average_rating = $ratings->avg('stars') ?? 0;
            $business->total_ratings = $ratings->count();

            return $business;
        });

        return response()->json([
            'success' => true,
            'businesses' => $businesses,
            'total' => $businesses->total(),
            'current_page' => $businesses->currentPage(),
            'last_page' => $businesses->lastPage(),
            'per_page' => $businesses->perPage()
        ]);
    }

    /**
     * Get filter options (cities and categories from approved businesses)
     */
    public function getFilters()
    {
        $cities = City::with('municipalities')->get()->map(function ($city) {
            return [
                'name' => $city->name,
                'municipalities' => $city->municipalities->pluck('name')->sort()->values()
            ];
        });

        $categories = Business::where('status', 'approved')
            ->whereNotNull('main_category')
            ->distinct()
            ->pluck('main_category')
            ->sort()
            ->values();

        return response()->json([
            'success' => true,
            'filters' => [
                'cities' => $cities,
                'categories' => $categories
            ]
        ]);
    }

    /**
     * Get single business details (public view)
     */
    public function getBusiness($id)
    {
        $business = Business::with(['user', 'reservations'])
            ->where('status', 'approved')
            ->findOrFail($id);

        $business->reservation_count = $business->reservations()->count();

        // Get ratings data
        $ratings = $business->ratings;
        $business->average_rating = $ratings->avg('stars') ?? 0;
        $business->total_ratings = $ratings->count();

        return response()->json([
            'success' => true,
            'business' => $business
        ]);
    }

    /**
     * Get available time slots for a business on a specific date (legacy method)
     */
    public function getAvailableSlots($id, Request $request)
    {
        $business = Business::findOrFail($id);

        // Check if business is approved
        if ($business->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Овој бизнис не е достапен'
            ], 404);
        }

        $date = $request->query('date');
        if (!$date) {
            return response()->json([
                'success' => false,
                'message' => 'Параметарот за датум е задолжителен'
            ], 400);
        }

        // Validate date format
        try {
            $selectedDate = new \DateTime($date);
            $today = new \DateTime();
            $today->setTime(0, 0, 0);

            if ($selectedDate < $today) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не може да се резервираат термини за минати датуми'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Невалиден формат на датум'
            ], 400);
        }

        // Get day of week from date
        $dayOfWeek = strtolower($selectedDate->format('l')); // monday, tuesday, etc.

        // Get business working hours
        $workingHours = $business->working_hours;

        // If working hours is a JSON string, decode it
        if (is_string($workingHours)) {
            $workingHours = json_decode($workingHours, true);
        }

        // If no working hours are set, return empty slots
        if (!$workingHours || !is_array($workingHours)) {
            return response()->json([
                'success' => true,
                'available_slots' => [],
                'message' => 'Работните часови на бизнисот не се конфигурирани'
            ]);
        }

        // Check if the day has working hours configuration
        if (!isset($workingHours[$dayOfWeek])) {
            return response()->json([
                'success' => true,
                'available_slots' => [],
                'message' => 'Бизнисот е затворен овој ден'
            ]);
        }

        $dayHours = $workingHours[$dayOfWeek];

        // Check if business is closed that day
        if (isset($dayHours['closed']) && $dayHours['closed']) {
            return response()->json([
                'success' => true,
                'available_slots' => [],
                'message' => 'Business is closed on this day'
            ]);
        }

        // Check if open and close times are set
        if (!isset($dayHours['open']) || !isset($dayHours['close'])) {
            return response()->json([
                'success' => true,
                'available_slots' => [],
                'message' => 'Работните часови на бизнисот не се конфигурирани за овој ден'
            ]);
        }

        // Check if times are empty strings
        if (empty($dayHours['open']) || empty($dayHours['close'])) {
            return response()->json([
                'success' => true,
                'available_slots' => [],
                'message' => 'Работните часови на бизнисот не се конфигурирани за овој ден'
            ]);
        }

        // Generate time slots every hour within working hours
        $availableSlots = [];
        $openTime = strtotime($dayHours['open']);
        $closeTime = strtotime($dayHours['close']);

        // Start from opening time
        $currentTime = $openTime;

        // Generate slots every hour
        while ($currentTime < $closeTime) {
            $timeString = date('H:i', $currentTime);
            $availableSlots[] = $timeString;

            // Add 1 hour
            $currentTime = strtotime('+1 hour', $currentTime);
        }

        // Get existing reservations for this date and business
        $existingReservations = \App\Models\Reservation::where('business_id', $business->id)
            ->where('date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->pluck('time')
            ->toArray();

        // Remove booked slots from available slots
        $availableSlots = array_diff($availableSlots, $existingReservations);

        return response()->json([
            'success' => true,
            'available_slots' => array_values($availableSlots),
            'working_hours' => $dayHours,
            'booked_slots' => $existingReservations
        ]);
    }

    /**
     * Get all available slots created by business owner
     */
    public function getAllAvailableSlots($id)
    {
        $business = Business::findOrFail($id);

        // Check if business is approved
        if ($business->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Овој бизнис не е достапен'
            ], 404);
        }

        // Get all available slots created by the business owner that still have capacity
        // Return all available slots - time filtering will be done on frontend
        $availableSlots = \App\Models\Reservation::where('business_id', $business->id)
            ->where('status', 'available')
            ->where('user_id', $business->user_id) // Only slots created by business owner
            ->whereRaw('capacity > (SELECT COUNT(*) FROM reservations r2 WHERE r2.business_id = reservations.business_id AND r2.date = reservations.date AND r2.time = reservations.time AND r2.status IN ("pending", "confirmed"))')
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->get();

        \Log::info('Returning ' . $availableSlots->count() . ' available slots (time filtering moved to frontend)');

        // Add current bookings count to each slot
        $availableSlots->transform(function ($slot) {
            $currentBookings = \App\Models\Reservation::where('business_id', $slot->business_id)
                ->where('date', $slot->date)
                ->where('time', $slot->time)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();

            $slot->current_bookings = $currentBookings;
            $slot->remaining_capacity = $slot->capacity - $currentBookings;

            return $slot;
        });

        return response()->json([
            'success' => true,
            'available_slots' => $availableSlots
        ]);
    }
}
