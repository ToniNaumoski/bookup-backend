<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminController extends Controller
{
    /**
     * Get all users
     */
    public function getUsers()
    {
        // Check if user is super_admin
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $users = User::all();
        
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * Get all businesses
     */
    public function getBusinesses()
    {
        // Check if user is super_admin
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $businesses = Business::with('user')->get();
        
        return response()->json([
            'success' => true,
            'businesses' => $businesses
        ]);
    }

    /**
     * Get all reservations
     */
    public function getReservations()
    {
        // Check if user is super_admin
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $reservations = \App\Models\Reservation::with(['user', 'business'])->get();
        
        return response()->json([
            'success' => true,
            'reservations' => $reservations
        ]);
    }

    /**
     * Dashboard statistics
     */
    public function dashboard()
    {
        // Check if user is super_admin
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $stats = [
            'total_users' => User::count(),
            'total_businesses' => Business::count(),
            'total_reservations' => \App\Models\Reservation::count(),
            'active_reservations' => \App\Models\Reservation::where('status', 'active')->count(),
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Approve a business
     */
    public function approveBusiness($id)
    {
        // Check if user is super_admin
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $business = Business::findOrFail($id);
        $business->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'Business approved successfully.',
            'business' => $business
        ]);
    }

    /**
     * Reject a business
     */
    public function rejectBusiness($id)
    {
        // Check if user is super_admin
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $business = Business::findOrFail($id);
        $business->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Business rejected successfully.',
            'business' => $business
        ]);
    }

    /**
     * Cancel a reservation
     */
    public function cancelReservation($id)
    {
        // Check if user is super_admin
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $reservation = \App\Models\Reservation::findOrFail($id);
        $reservation->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Reservation cancelled successfully.',
            'reservation' => $reservation
        ]);
    }
}