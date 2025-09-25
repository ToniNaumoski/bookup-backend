<?php
use App\Http\Controllers\AdminMessageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CreateBusinessController;
use App\Http\Controllers\PublicBusinessController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SuperAdminController;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;


// Verify email callback
// Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//     $request->fulfill(); // Marks email as verified
//     return response()->json(['message' => 'Email verified successfully!']);
// })->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

// // Resend verification email
// Route::post('/email/verification-notification', function (Request $request) {
//     $request->user()->sendEmailVerificationNotification();
//     return response()->json(['message' => 'Verification link sent!']);
// })->middleware(['auth:sanctum']);

Broadcast::channel('admin-notifications', function ($user) {
    // Return true if the authenticated user is an admin/business owner
    return in_array($user->role, ['super_admin', 'business']);
});

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = \App\Models\User::findOrFail($id);

    if (! hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
        return response()->json(['message' => 'Invalid verification link.'], 403);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email address is already verified.']);
    }

    $user->markEmailAsVerified();

    return response()->json(['message' => 'Email address successfully verified!']);
})->middleware(['signed'])->name('verification.verify');

// Resend verification email
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return response()->json(['message' => 'Линк за верификација е испратен на вашата емаил адреса!']);
})->middleware(['auth:sanctum'])->name('verification.send');

// Public routes (no authentication required)
Route::get('/businesses/approved', [PublicBusinessController::class, 'getApprovedBusinesses']);
Route::get('/businesses/filters', [PublicBusinessController::class, 'getFilters']);
Route::get('/businesses/{id}', [PublicBusinessController::class, 'getBusiness']);
Route::get('/businesses/{id}/available-slots', [PublicBusinessController::class, 'getAvailableSlots']);
Route::get('/businesses/{id}/all-available-slots', [PublicBusinessController::class, 'getAllAvailableSlots']);
Route::get('/reservations/available', [ReservationController::class, 'available']);
Route::get('/ratings/business/{businessId}', [RatingController::class, 'getBusinessRatings']);
// Route::middleware(['auth:sanctum','verified'])->group(function () {
Route::middleware(['auth:sanctum'])->group(function () {
    // Get business form (returns business data if exists, else categories & cities)
Route::get('/business/form', [CreateBusinessController::class, 'form'])
    ->name('business.form');
    Route::post('/business/create', [CreateBusinessController::class, 'store'])
    ->name('business.store');
    Route::post('/business/mark-message-read', [CreateBusinessController::class, 'markMessageAsRead'])
    ->name('business.mark-message-read');
    // Store business data
  //  Route::post('/business', [CreateBusinessController::class, 'store'])->name('business.store');
});

Route::post('/register/user', [AuthController::class, 'registerUser']);
Route::post('/register/business', [AuthController::class, 'registerBusiness']);
Route::post('/login', [AuthController::class, 'login']);

// // Test email endpoint
// Route::post('/test-email', function (Request $request) {
//     $request->validate([
//         'email' => 'required|email'
//     ]);

//     try {
//         $user = \App\Models\User::first() ?? \App\Models\User::factory()->create([
//             'name' => 'Test User',
//             'email' => $request->email,
//             'email_verified_at' => null
//         ]);

//         $user->sendEmailVerificationNotification();

//         return response()->json([
//             'message' => 'Тест емаилот е успешно испратен! Проверете го вашиот inbox/spam фолдер.'
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'message' => 'Грешка при испраќање на емаил: ' . $e->getMessage()
//         ], 500);
//     }
// });
// Route::middleware(['auth:sanctum', 'verified'])->group(function () {
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Reservation routes
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::put('/reservations/{id}/cancel', [ReservationController::class, 'cancel']);
    Route::put('/reservations/{id}/confirm', [ReservationController::class, 'confirm']);
    Route::post('/reservations/{id}/message', [ReservationController::class, 'sendMessage']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);

    // Rating routes
    Route::post('/ratings/user', [RatingController::class, 'submitUserRating']);
    Route::post('/ratings/business', [RatingController::class, 'submitBusinessRating']);
    Route::get('/ratings/user/{userId}', [RatingController::class, 'getUserRatings']);
    Route::get('/ratings/can-rate/{reservationId}', [RatingController::class, 'canRateReservation']);
    
    // Super Admin routes with middleware protection
    Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/businesses/pending-reviews', [SuperAdminController::class, 'getPendingReviews']);
        Route::post('/businesses/{id}/review', [SuperAdminController::class, 'reviewBusiness']);
        Route::get('/businesses/{id}/review', [SuperAdminController::class, 'getBusinessForReview']);
        Route::post('/businesses/{id}/message', [SuperAdminController::class, 'sendMessageToBusiness']);

        Route::get('/users', [SuperAdminController::class, 'getUsers']);
        Route::get('/businesses', [SuperAdminController::class, 'getBusinesses']);
        Route::get('/reservations', [SuperAdminController::class, 'getReservations']);
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard']);
        Route::get('/stats', [SuperAdminController::class, 'getDashboardStats']);

        // User management routes
        Route::get('/users/{id}', [SuperAdminController::class, 'getUserDetails']);
        Route::put('/users/{id}/block', [SuperAdminController::class, 'blockUser']);
        Route::put('/users/{id}/unblock', [SuperAdminController::class, 'unblockUser']);
        Route::put('/users/{id}/suspend', [SuperAdminController::class, 'suspendUser']);
        Route::put('/users/{id}/unsuspend', [SuperAdminController::class, 'unsuspendUser']);
        Route::put('/users/{id}/role', [SuperAdminController::class, 'updateUserRole']);
        Route::get('/users/status/{status}', [SuperAdminController::class, 'getUsersByStatus']);
        Route::post('/users/{id}/message', [SuperAdminController::class, 'sendMessageToUser']);

        // Business management routes
        Route::get('/businesses/{id}', [SuperAdminController::class, 'getBusinessDetails']);

        // Business review routes
        // Route::get('/businesses/{id}/review', [SuperAdminController::class, 'getBusinessForReview']);
        // Route::post('/businesses/{id}/review', [SuperAdminController::class, 'reviewBusiness']);
        // Route::post('/businesses/{id}/message', [SuperAdminController::class, 'sendMessageToBusiness']);

        // Admin message routes
        Route::get('/messages/all', [\App\Http\Controllers\AdminMessageController::class, 'getAllMessages']);
        Route::get('/messages/user/{userId}', [\App\Http\Controllers\AdminMessageController::class, 'getUserMessages']);
        Route::post('/messages/user/{userId}', [\App\Http\Controllers\AdminMessageController::class, 'sendMessageToUser']);
        Route::put('/messages/{messageId}/read', [\App\Http\Controllers\AdminMessageController::class, 'markAsRead']);

        // Legacy routes (keeping for backward compatibility)
        Route::put('/businesses/{id}/approve', [SuperAdminController::class, 'approveBusiness']);
        Route::put('/businesses/{id}/reject', [SuperAdminController::class, 'rejectBusiness']);
        Route::put('/reservations/{id}/cancel', [SuperAdminController::class, 'cancelReservation']);

        // Admin messaging routes
        Route::get('/messages/all', [AdminMessageController::class, 'getAllMessages']);
        Route::get('/messages/user/{userId}', [AdminMessageController::class, 'getUserMessages']);
        Route::post('/messages/user/{userId}', [AdminMessageController::class, 'sendMessageToUser']);
    });

    // User messaging routes (for regular users and businesses)
    Route::get('/messages', [AdminMessageController::class, 'getMyMessages']);
    Route::post('/messages', [AdminMessageController::class, 'sendMessageToAdmin']);
    Route::post('/messages/feedback', [AdminMessageController::class, 'submitFeedback']);
    Route::put('/messages/{messageId}/read', [AdminMessageController::class, 'markAsRead']);
    Route::put('/user-messages/{messageIndex}/read', [AdminMessageController::class, 'markUserAdminMessageAsRead']);
    Route::put('/user-messages/mark-read', [AdminMessageController::class, 'markSystemMessageAsRead']);
    Route::get('/messages/unread-count', [AdminMessageController::class, 'getUnreadCount']);
});
