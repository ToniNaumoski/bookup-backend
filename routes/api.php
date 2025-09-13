<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CreateBusinessController;
use App\Http\Controllers\PublicBusinessController;
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
    return $user->is_admin || ($user->is_business_owner ?? false);
});

Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    $user = \App\Models\User::findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->email))) {
        return response()->json(['message' => 'Invalid verification link.'], 400);
    }

    $user->markEmailAsVerified();

    return response()->json(['message' => 'Email verified successfully!']);
})->name('verification.verify');

// Public routes (no authentication required)
Route::get('/businesses/approved', [PublicBusinessController::class, 'getApprovedBusinesses']);
Route::get('/businesses/filters', [PublicBusinessController::class, 'getFilters']);
Route::get('/businesses/{id}', [PublicBusinessController::class, 'getBusiness']);
Route::get('/businesses/{id}/available-slots', [PublicBusinessController::class, 'getAvailableSlots']);
Route::get('/businesses/{id}/all-available-slots', [PublicBusinessController::class, 'getAllAvailableSlots']);
Route::get('/reservations/available', [ReservationController::class, 'available']);
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
// Route::middleware(['auth:sanctum', 'verified'])->group(function () {
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Reservation routes
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::put('/reservations/{id}/cancel', [ReservationController::class, 'cancel']);
    Route::put('/reservations/{id}/confirm', [ReservationController::class, 'confirm']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
    
    // Super Admin routes with middleware protection
    Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
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
        Route::put('/users/{id}/role', [SuperAdminController::class, 'updateUserRole']);
        Route::get('/users/status/{status}', [SuperAdminController::class, 'getUsersByStatus']);
        Route::post('/users/{id}/message', [SuperAdminController::class, 'sendMessageToUser']);

        // Business review routes
        Route::get('/businesses/{id}/review', [SuperAdminController::class, 'getBusinessForReview']);
        Route::post('/businesses/{id}/review', [SuperAdminController::class, 'reviewBusiness']);
        Route::post('/businesses/{id}/message', [SuperAdminController::class, 'sendMessageToBusiness']);
        Route::get('/businesses/pending-reviews', [SuperAdminController::class, 'getPendingReviews']);

        // Legacy routes (keeping for backward compatibility)
        Route::put('/businesses/{id}/approve', [SuperAdminController::class, 'approveBusiness']);
        Route::put('/businesses/{id}/reject', [SuperAdminController::class, 'rejectBusiness']);
        Route::put('/reservations/{id}/cancel', [SuperAdminController::class, 'cancelReservation']);
    });
});
