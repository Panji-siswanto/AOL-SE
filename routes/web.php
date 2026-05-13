<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\ListingRequestController;
use App\Http\Controllers\Admin\UserVerificationRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpaceRegistrationController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. THE TRAFFIC COP (Single Entry Point)
|--------------------------------------------------------------------------
| Everyone landing on '/' passes through here. Admins are instantly 
| routed to the admin area. Renters and Owners share the main dashboard 
| view, which renders content dynamically based on their current role.
*/
Route::get('/', function () {
    if (auth()->check() && auth()->user()->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| 2. GENERAL AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
| Accessible to any logged-in user (Renters, Owners, etc.)
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Profile Management (Default Breeze Routes)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Account Verification Flow (KTP & Selfie Upload)
    Route::get('/verify-account', [VerificationController::class, 'index'])->name('verification.index');
    Route::post('/verify-account', [VerificationController::class, 'store'])->name('verification.store');

});


/*
|--------------------------------------------------------------------------
| 3. VERIFIED USER ROUTES (Spatie Permissions Protected)
|--------------------------------------------------------------------------
| Only users who have been successfully vetted and hold the specific 
| permission can register or manage spaces.
*/
Route::middleware(['auth', 'verified', 'permission:submit space registration'])->group(function () {
    // Verified User Routes (Spatie Permissions Protected)
    Route::get('/space-registrations/create', [SpaceRegistrationController::class, 'create'])->name('space-registrations.create');
    Route::post('/space-registrations', [SpaceRegistrationController::class, 'store'])->name('space-registrations.store');
    
});


/*
|--------------------------------------------------------------------------
| 4. SECURE ADMIN COMMAND CENTER
|--------------------------------------------------------------------------
| Completely isolated backend. Requires valid session AND the 'admin' role.
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin']) 
    ->group(function () {
        
        // Admin Overview Dashboard (/admin)
        Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');

        // User Identity Verification Antrean
        Route::get('/user-verifications', [UserVerificationRequestController::class, 'index'])->name('user-verifications.index');
        Route::get('/user-verifications/{verificationLog}', [UserVerificationRequestController::class, 'show'])->name('user-verifications.show');
        Route::post('/user-verifications/{verificationLog}/approve', [UserVerificationRequestController::class, 'approve'])->name('user-verifications.approve');
        Route::post('/user-verifications/{verificationLog}/reject', [UserVerificationRequestController::class, 'reject'])->name('user-verifications.reject');
        
        // Space Listing Requests (Moderation queue for new properties)
        Route::get('/listing-requests', [ListingRequestController::class, 'index'])->name('listing-requests.index');
        Route::get('/listing-requests/{registration}', [ListingRequestController::class, 'show'])->name('listing-requests.show');
        Route::post('/listing-requests/{registration}/approve', [ListingRequestController::class, 'approve'])->name('listing-requests.approve');
        Route::post('/listing-requests/{registration}/reject', [ListingRequestController::class, 'reject'])->name('listing-requests.reject');
        
    });

// Load standard Breeze authentication routes (login, register, passwords)       
require __DIR__.'/auth.php';