<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ListingRequestController;
use App\Http\Controllers\Admin\UserVerificationRequestController;
use App\Http\Controllers\Owner\spaces\SpaceController;
use App\Http\Controllers\Owner\spaces\SpaceRegistrationController;
use App\Http\Controllers\Public\BookmarkController;
use App\Http\Controllers\Public\SpaceDiscoveryController;
use App\Http\Controllers\Renter\RentRequestController;

// Public
Route::get('/', [SpaceDiscoveryController::class, 'index'])->name('dashboard');
Route::get('/spaces/{space}', [SpaceDiscoveryController::class, 'show'])->name('spaces.show');

// Bookmarks
Route::middleware('auth')->group(function () {
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');    
    Route::post('/spaces/{space}/bookmark', [BookmarkController::class, 'toggle'])->name('spaces.bookmark');
});   

// Renter
Route::middleware(['auth', 'verified'])->prefix('rents')->name('rents.')->group(function () {
    Route::get('/', [RentRequestController::class, 'index'])->name('index');
    Route::get('/{space}/apply', [RentRequestController::class, 'create'])->name('create');
    Route::post('/{space}/apply', [RentRequestController::class, 'store'])->name('store');
});

// Accounts
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/verify-account', [VerificationController::class, 'index'])->name('verification.index');
    Route::post('/verify-account', [VerificationController::class, 'store'])->name('verification.store');
});

// Owner
Route::middleware(['auth', 'verified'])->prefix('owner')->name('owner.')->group(function () {
    Route::resource('spaces', SpaceController::class);
    Route::patch('spaces/{space}/status', [SpaceController::class, 'updateStatus'])->name('spaces.status.update');
    
    Route::prefix('spaces/registrations')->name('spaces.registrations.')->group(function () {
        Route::get('/create', [SpaceRegistrationController::class, 'create'])->name('create');
        Route::post('/', [SpaceRegistrationController::class, 'store'])->name('store');
        Route::get('/{registration}', [SpaceRegistrationController::class, 'show'])->name('show-registration');
        Route::post('/{registration}/photos/reorder', [SpaceRegistrationController::class, 'reorderPhotos'])->name('photos.reorder');
    });
});

// Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Verifications
    Route::get('/user-verifications', [UserVerificationRequestController::class, 'index'])->name('user-verifications.index');
    Route::get('/user-verifications/history', [UserVerificationRequestController::class, 'history'])->name('user-verifications.history');
    Route::get('/user-verifications/{verificationLog}', [UserVerificationRequestController::class, 'show'])->name('user-verifications.show');
    Route::post('/user-verifications/{verificationLog}/approve', [UserVerificationRequestController::class, 'approve'])->name('user-verifications.approve');
    Route::post('/user-verifications/{verificationLog}/reject', [UserVerificationRequestController::class, 'reject'])->name('user-verifications.reject');

    // Listing Requests
    Route::get('/listing-requests', [ListingRequestController::class, 'index'])->name('listing-requests.index');
    Route::get('/listing-requests/history', [ListingRequestController::class, 'history'])->name('listing-requests.history');
    Route::get('/listing-requests/{registration}', [ListingRequestController::class, 'show'])->name('listing-requests.show');
    Route::post('/listing-requests/{registration}/approve', [ListingRequestController::class, 'approve'])->name('listing-requests.approve');
    Route::post('/listing-requests/{registration}/reject', [ListingRequestController::class, 'reject'])->name('listing-requests.reject');
});

require __DIR__.'/auth.php';