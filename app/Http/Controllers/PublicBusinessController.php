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
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('city') && $request->city) {
            $query->where('city', $request->city);
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

        // Paginate results
        $businesses = $query->paginate(12);

        // Add reservation count to each business
        $businesses->getCollection()->transform(function ($business) {
            $business->reservation_count = $business->reservations()->count();
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
        $cities = Business::where('status', 'approved')
            ->whereNotNull('city')
            ->distinct()
            ->pluck('city')
            ->sort()
            ->values();

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

        return response()->json([
            'success' => true,
            'business' => $business
        ]);
    }

    /**
     * Get available time slots for a business on a specific date
     */
    public function getAvailableSlots($id, Request $request)
    {
        $business = Business::findOrFail($id);

        // Check if business is approved
        if ($business->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'This business is not available'
            ], 404);
        }

        $date = $request->query('date');
        if (!$date) {
            return response()->json([
                'success' => false,
                'message' => 'Date parameter is required'
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
                    'message' => 'Cannot book appointments for past dates'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date format'
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
                'message' => 'Business working hours not configured'
            ]);
        }

        // Check if the day has working hours configuration
        if (!isset($workingHours[$dayOfWeek])) {
            return response()->json([
                'success' => true,
                'available_slots' => [],
                'message' => 'Business is closed on this day'
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
                'message' => 'Business hours not configured for this day'
            ]);
        }

        // Check if times are empty strings
        if (empty($dayHours['open']) || empty($dayHours['close'])) {
            return response()->json([
                'success' => true,
                'available_slots' => [],
                'message' => 'Business hours not configured for this day'
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
}
