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

    /**
     * Review a business with remarks and optional field updates
     */
    public function reviewBusiness(Request $request, $id)
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $request->validate([
            'action' => 'required|in:approve,reject,request_changes',
            'remarks' => 'nullable|string|max:1000',
            'message' => 'nullable|string|max:500',
            'updated_fields' => 'nullable|array',
            'updated_fields.*' => 'string'
        ]);

        $business = Business::findOrFail($id);
        $admin = Auth::user();

        // Update business fields if provided
        if ($request->has('updated_fields') && is_array($request->updated_fields)) {
            foreach ($request->updated_fields as $field => $value) {
                if (in_array($field, ['name', 'description', 'main_category', 'sub_category', 'city', 'street', 'street_number'])) {
                    $business->$field = $value;
                }
            }
        }

        // Update review status and admin info
        $business->review_status = $request->action;
        $business->reviewed_by = $admin->id;
        $business->last_reviewed_at = now();

        // Add admin remarks
        if ($request->remarks) {
            $business->admin_remarks = $request->remarks;
        }

        // Add admin message if provided
        if ($request->message) {
            $messages = $business->admin_messages ?? [];
            $messages[] = [
                'message' => $request->message,
                'type' => $request->action,
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'created_at' => now()->toISOString()
            ];
            $business->admin_messages = $messages;
        }

        // Update main status based on action
        if ($request->action === 'approve') {
            $business->status = 'approved';
        } elseif ($request->action === 'reject') {
            $business->status = 'rejected';
        } elseif ($request->action === 'request_changes') {
            $business->status = 'pending';
            $business->review_status = 'needs_revision';
        }

        $business->save();

        return response()->json([
            'success' => true,
            'message' => 'Business review completed successfully.',
            'business' => $business->load(['user', 'reviewer'])
        ]);
    }

    /**
     * Get detailed business information for admin review
     */
    public function getBusinessForReview($id)
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $business = Business::with(['user', 'reviewer', 'reservations'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'business' => $business
        ]);
    }

    /**
     * Get businesses pending review
     */
    public function getPendingReviews()
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $businesses = Business::with(['user', 'reviewer'])
            ->whereIn('review_status', ['pending', 'needs_revision'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'businesses' => $businesses
        ]);
    }

    /**
     * Send message to business owner
     */
    public function sendMessageToBusiness(Request $request, $id)
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $request->validate([
            'message' => 'required|string|max:500',
            'type' => 'required|in:info,warning,error'
        ]);

        $business = Business::findOrFail($id);
        $admin = Auth::user();

        $messages = $business->admin_messages ?? [];
        $messages[] = [
            'message' => $request->message,
            'type' => $request->type,
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'created_at' => now()->toISOString()
        ];

        $business->admin_messages = $messages;
        $business->save();

        return response()->json([
            'success' => true,
            'message' => 'Message sent to business owner successfully.',
            'business' => $business
        ]);
    }
}
