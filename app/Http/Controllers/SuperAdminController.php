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

        $users = User::with(['business', 'reservations', 'businessRatings', 'receivedRatings'])->get();

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

        $businesses = Business::with(['user', 'ratings'])->get();
        
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

        $business = Business::with(['user', 'reviewer', 'reservations', 'ratings.user'])->findOrFail($id);

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

        $businesses = Business::with(['user', 'reviewer', 'ratings'])
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

    /**
     * Block a user account
     */
    public function blockUser(Request $request, $id)
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $user = User::findOrFail($id);
        $admin = Auth::user();

        // Prevent blocking super admins
        if ($user->role === 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot block super admin accounts.'
            ], 403);
        }

        $user->update([
            'status' => 'blocked',
            'blocked_at' => now(),
            'block_reason' => $request->reason,
            'blocked_by' => $admin->id
        ]);

        // Send message to user
        $messages = $user->admin_messages ?? [];
        $messages[] = [
            'message' => "Your account has been blocked for the following reason: {$request->reason}. Please contact support if you believe this is an error.",
            'type' => 'error',
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'created_at' => now()->toISOString()
        ];
        $user->admin_messages = $messages;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User account blocked successfully.',
            'user' => $user->load('blockedBy')
        ]);
    }

    /**
     * Unblock a user account
     */
    public function unblockUser($id)
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $user = User::findOrFail($id);

        $user->update([
            'status' => 'active',
            'blocked_at' => null,
            'block_reason' => null,
            'blocked_by' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User account unblocked successfully.',
            'user' => $user
        ]);
    }

    /**
     * Unsuspend a user account
     */
    public function unsuspendUser($id)
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $user = User::findOrFail($id);

        // Only allow unsuspending suspended users
        if ($user->status !== 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'User is not suspended.'
            ], 422);
        }

        $user->update([
            'status' => 'active',
            'blocked_at' => null,
            'block_reason' => null,
            'blocked_by' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User account unsuspended successfully.',
            'user' => $user
        ]);
    }

    /**
     * Suspend a user account
     */
    public function suspendUser(Request $request, $id)
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
            'duration' => 'nullable|integer|min:1|max:365' // days
        ]);

        $user = User::findOrFail($id);
        $admin = Auth::user();

        // Prevent suspending super admins
        if ($user->role === 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot suspend super admin accounts.'
            ], 403);
        }

        $user->update([
            'status' => 'suspended',
            'blocked_at' => now(),
            'block_reason' => $request->reason,
            'blocked_by' => $admin->id
        ]);

        // Send message to user
        $messages = $user->admin_messages ?? [];
        $messages[] = [
            'id' => uniqid('msg_', true),
            'message' => "Your account has been suspended for the following reason: {$request->reason}. Please contact support if you believe this is an error.",
            'type' => 'warning',
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'created_at' => now()->toISOString(),
            'is_read' => false
        ];
        $user->admin_messages = $messages;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User account suspended successfully.',
            'user' => $user->load('blockedBy')
        ]);
    }

    /**
     * Get detailed user information
     */
    public function getUserDetails($id)
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $user = User::with(['business', 'reservations', 'blockedBy', 'businessRatings', 'receivedRatings.business'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Get detailed business information
     */
    public function getBusinessDetails($id)
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $business = Business::with(['user', 'reservations', 'ratings.user'])->findOrFail($id);

        \Log::info('Business details requested for ID: ' . $id);
        \Log::info('Business ratings count: ' . $business->ratings->count());
        \Log::info('Business ratings: ', $business->ratings->toArray());

        return response()->json([
            'success' => true,
            'business' => $business
        ]);
    }

    /**
     * Update user role
     */
    public function updateUserRole(Request $request, $id)
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $request->validate([
            'role' => 'required|in:user,business_owner,admin'
        ]);

        $user = User::findOrFail($id);
        $admin = Auth::user();

        // Prevent changing super admin roles
        if ($user->role === 'super_admin' || $request->role === 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify super admin roles.'
            ], 403);
        }

        $user->update(['role' => $request->role]);

        return response()->json([
            'success' => true,
            'message' => 'User role updated successfully.',
            'user' => $user
        ]);
    }

    /**
     * Get users by status
     */
    public function getUsersByStatus($status)
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $validStatuses = ['active', 'blocked', 'suspended'];
        if (!in_array($status, $validStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status provided.'
            ], 400);
        }

        $users = User::with(['business', 'blockedBy', 'businessRatings', 'receivedRatings'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * Send message to user
     */
    public function sendMessageToUser(Request $request, $id)
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

        $user = User::findOrFail($id);
        $admin = Auth::user();

        // For now, we'll store messages in user meta or create a separate messages table
        // This is a placeholder for the messaging system
        $messages = $user->admin_messages ?? [];
        $messages[] = [
            'message' => $request->message,
            'type' => $request->type,
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'created_at' => now()->toISOString()
        ];

        $user->admin_messages = $messages;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Message sent to user successfully.',
            'user' => $user
        ]);
    }

    /**
     * Get admin dashboard statistics
     */
    public function getDashboardStats()
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super admin access required.'
            ], 403);
        }

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'blocked_users' => User::where('status', 'blocked')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'total_businesses' => Business::count(),
            'approved_businesses' => Business::where('status', 'approved')->count(),
            'pending_businesses' => Business::where('status', 'pending')->count(),
            'total_reservations' => \App\Models\Reservation::count(),
            'recent_users' => User::orderBy('created_at', 'desc')->limit(5)->get(),
            'recent_businesses' => Business::with(['user', 'ratings'])->orderBy('created_at', 'desc')->limit(5)->get(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
