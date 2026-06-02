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
})->name('dashboard');



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
Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/space-registrations', [SpaceRegistrationController::class, 'index'])->name('space-registrations.index');
    Route::get('/space-registrations/create', [SpaceRegistrationController::class, 'create'])->name('space-registrations.create');
    Route::post('/space-registrations', [SpaceRegistrationController::class, 'store'])->name('space-registrations.store');
    Route::get('/space-registrations/{id}', [SpaceRegistrationController::class, 'show'])->name('space-registrations.show');
    Route::get('/space-registrations/{id}/edit', [SpaceRegistrationController::class, 'edit'])->name('space-registrations.edit');
    Route::put('/space-registrations/{id}', [SpaceRegistrationController::class, 'update'])->name('space-registrations.update');
    Route::delete('/space-registrations/{id}', [SpaceRegistrationController::class, 'destroy'])->name('space-registrations.destroy');
    Route::post('/space-registrations/{id}/photos/reorder', [SpaceRegistrationController::class, 'reorderPhotos'])->name('space-registrations.photos.reorder');
});
/*
|--------------------------------------------------------------------------
| 4. SECURE ADMIN COMMAND CENTER
|--------------------------------------------------------------------------
| Completely isolated backend. Requires valid session AND the 'admin' role.
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')
    ->group(function () {
        
        // Admin Overview Dashboard (/admin)
        Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');

        // User Identity Verification Antrean
        Route::get('/user-verifications', [UserVerificationRequestController::class, 'index'])->name('user-verifications.index');
        Route::get('/user-verifications/history', [UserVerificationRequestController::class, 'history'])->name('user-verifications.history');
        Route::get('/user-verifications/{verificationLog}', [UserVerificationRequestController::class, 'show'])->name('user-verifications.show');
        Route::post('/user-verifications/{verificationLog}/approve', [UserVerificationRequestController::class, 'approve'])->name('user-verifications.approve');
        Route::post('/user-verifications/{verificationLog}/reject', [UserVerificationRequestController::class, 'reject'])->name('user-verifications.reject');

        // Space Listing Requests (Moderation queue for new properties)
        Route::get('/listing-requests', [ListingRequestController::class, 'index'])->name('listing-requests.index');
        Route::get('/listing-requests/history', [ListingRequestController::class, 'history'])->name('listing-requests.history');
        Route::get('/listing-requests/{registration}', [ListingRequestController::class, 'show'])->name('listing-requests.show');
        Route::post('/listing-requests/{registration}/approve', [ListingRequestController::class, 'approve'])->name('listing-requests.approve');
        Route::post('/listing-requests/{registration}/reject', [ListingRequestController::class, 'reject'])->name('listing-requests.reject');
    });


    Route::get('/space-details/{space}', [ListingRequestController::class, 'show'])->name('space-details.show');
// Load standard Breeze authentication routes (login, register, passwords)       
require __DIR__.'/auth.php';