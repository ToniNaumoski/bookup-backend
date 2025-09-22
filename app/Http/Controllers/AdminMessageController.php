<?php

namespace App\Http\Controllers;

use App\Models\AdminMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminMessageController extends Controller
{
    /**
     * Get all messages for a specific user (admin view)
     */
    public function getUserMessages(Request $request, $userId)
    {
        $admin = Auth::user();

        // Check if user is super admin
        if ($admin->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        \Log::info('getUserMessages called with userId: ' . $userId);
        $user = User::findOrFail($userId);
        \Log::info('User found: ' . $user->id . ' - ' . $user->name);

        // Get messages between admin and specific user
        $messages = AdminMessage::where(function($query) use ($admin, $user) {
            $query->where(function($q) use ($admin, $user) {
                $q->where('sender_id', $admin->id)
                  ->where('receiver_id', $user->id);
            })->orWhere(function($q) use ($admin, $user) {
                $q->where('sender_id', $user->id)
                  ->where('receiver_id', $admin->id);
            });
        })->with(['sender', 'receiver'])
          ->orderBy('created_at', 'asc')
          ->get();

        \Log::info('Found ' . $messages->count() . ' messages for user ' . $user->id);

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'user' => $user
        ]);
    }

    /**
     * Send message from admin to user
     */
    public function sendMessageToUser(Request $request, $userId)
    {
        $admin = Auth::user();

        // Check if user is super admin
        if ($admin->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'type' => 'in:info,warning,error'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($userId);

        $message = AdminMessage::create([
            'sender_id' => $admin->id,
            'receiver_id' => $user->id,
            'message' => $request->message,
            'type' => $request->type ?? 'info',
            'is_read' => false
        ]);

        $message->load(['sender', 'receiver']);

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Get messages for current authenticated user (user/business view)
     */
    public function getMyMessages(Request $request)
    {
        $user = Auth::user();

        // Get messages between user and any super admin
        $adminIds = User::where('role', 'super_admin')->pluck('id')->toArray();

        $messages = AdminMessage::where(function($query) use ($user, $adminIds) {
            $query->where('sender_id', $user->id)
                  ->whereIn('receiver_id', $adminIds);
        })->orWhere(function($query) use ($user, $adminIds) {
            $query->whereIn('sender_id', $adminIds)
                  ->where('receiver_id', $user->id);
        })->with(['sender', 'receiver'])
          ->orderBy('created_at', 'asc')
          ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Send message from current user to admin
     */
    public function sendMessageToAdmin(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find super admin
        $admin = User::where('role', 'super_admin')->first();
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found'
            ], 404);
        }

        $message = AdminMessage::create([
            'sender_id' => $user->id,
            'receiver_id' => $admin->id,
            'message' => $request->message,
            'type' => 'info',
            'is_read' => false
        ]);

        $message->load(['sender', 'receiver']);

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Request $request, $messageId)
    {
        $user = Auth::user();

        $message = AdminMessage::findOrFail($messageId);

        // Check if user is the receiver OR sender (for admin to mark their own sent messages)
        if ($message->receiver_id !== $user->id && $message->sender_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * Mark user admin message as read (for system messages stored in user model)
     */
    public function markUserAdminMessageAsRead(Request $request, $messageId)
    {
        $user = Auth::user();

        $messages = $user->admin_messages ?? [];

        $messageIndex = null;
        foreach ($messages as $index => $message) {
            if (isset($message['id']) && $message['id'] === $messageId) {
                $messageIndex = $index;
                break;
            }
        }

        if ($messageIndex === null) {
            return response()->json(['message' => 'Message not found'], 404);
        }

        $messages[$messageIndex]['is_read'] = true;
        $user->admin_messages = $messages;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * Mark system message as read by content and created_at (for when ID is not available)
     */
    public function markSystemMessageAsRead(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'created_at' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $messages = $user->admin_messages ?? [];

        $messageIndex = null;
        foreach ($messages as $index => $message) {
            if ($message['message'] === $request->message && $message['created_at'] === $request->created_at) {
                $messageIndex = $index;
                break;
            }
        }

        if ($messageIndex === null) {
            return response()->json(['message' => 'Message not found'], 404);
        }

        $messages[$messageIndex]['is_read'] = true;
        $user->admin_messages = $messages;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * Get all messages for admin (from all users and businesses)
     */
    public function getAllMessages(Request $request)
    {
        $admin = Auth::user();

        // Check if user is super admin
        if ($admin->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get feedback messages (notes and suggestions) - these should always be visible
        $feedbackMessages = AdminMessage::where('receiver_id', $admin->id)
            ->whereIn('type', ['note', 'suggestion'])
            ->with(['sender', 'receiver'])
            ->get();

        // Get the latest message from each regular conversation (excluding feedback)
        $regularMessages = AdminMessage::where(function($query) use ($admin) {
                $query->where('receiver_id', $admin->id)
                      ->orWhere('sender_id', $admin->id);
            })
            ->whereNotIn('type', ['note', 'suggestion']) // Exclude feedback messages
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($message) use ($admin) {
                // Group by the other user (not admin)
                return $message->sender_id === $admin->id ? $message->receiver_id : $message->sender_id;
            })
            ->map(function($conversation) {
                // Get the latest message from each conversation
                return $conversation->first();
            })
            ->values();

        // Combine feedback messages and regular conversation previews
        $allMessages = $feedbackMessages->concat($regularMessages)->sortByDesc('created_at')->values();

        return response()->json([
            'success' => true,
            'messages' => $allMessages->toArray()
        ]);
    }

    /**
     * Submit feedback/note/suggestion from user to admin
     */
    public function submitFeedback(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'type' => 'required|in:note,suggestion'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find super admin
        $admin = User::where('role', 'super_admin')->first();
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found'
            ], 404);
        }

        $message = AdminMessage::create([
            'sender_id' => $user->id,
            'receiver_id' => $admin->id,
            'message' => $request->message,
            'type' => $request->type,
            'is_read' => false
        ]);

        $message->load(['sender', 'receiver']);

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Get unread message count for current user
     */
    public function getUnreadCount(Request $request)
    {
        $user = Auth::user();

        $admin = User::where('role', 'super_admin')->first();
        if (!$admin) {
            return response()->json(['count' => 0]);
        }

        $count = AdminMessage::where('receiver_id', $user->id)
            ->where('sender_id', $admin->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
