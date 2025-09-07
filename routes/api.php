<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CreateBusinessController;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

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

Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    $user = \App\Models\User::findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->email))) {
        return response()->json(['message' => 'Invalid verification link.'], 400);
    }

    $user->markEmailAsVerified();

    return response()->json(['message' => 'Email verified successfully!']);
})->name('verification.verify');

Route::middleware(['auth:sanctum','verified'])->group(function () {
    // Get business form (returns business data if exists, else categories & cities)
Route::get('/business/form', [CreateBusinessController::class, 'form'])
    ->name('business.form');
    Route::post('/business/create', [CreateBusinessController::class, 'store'])
    ->name('business.store');
    // Store business data
  //  Route::post('/business', [CreateBusinessController::class, 'store'])->name('business.store');
});

Route::post('/register/user', [AuthController::class, 'registerUser']);
Route::post('/register/business', [AuthController::class, 'registerBusiness']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});