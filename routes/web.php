<?php

use App\Http\Controllers\Admin\ListingRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpaceRegistrationController;
use Illuminate\Support\Facades\Route;

// Owner
// Route::middleware(['auth:sanctum', 'permission:submit space registration'])->group(function () {
//     Route::post('/space-registrations', [SpaceRegistrationController::class, 'store']);
// });

// TEMPORARY FOR POSTMAN TESTING, set auth for later
// Route::get('/space-registrations', [SpaceRegistrationController::class, 'index']); 
// Route::post('/space-registrations', [SpaceRegistrationController::class, 'store']);
// Route::get('/space-registrations/{id}', [SpaceRegistrationController::class, 'show']); 

// Route::get('/admin/listing-requests', [ListingRequestController::class, 'index']);
// Route::get('/admin/listing-requests/{registration}', [ListingRequestController::class, 'show']);
// Route::post('/admin/listing-requests/{registration}/approve', [ListingRequestController::class, 'approve']);
// Route::post('/admin/listing-requests/{registration}/reject', [ListingRequestController::class, 'reject']);



    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

 
    Route::middleware(['permission:submit space registration'])->group(function () {
        Route::get('/space-registrations', [SpaceRegistrationController::class, 'index'])->name('space-registrations.index');
        Route::get('/space-registrations/create', [SpaceRegistrationController::class, 'create'])->name('space-registrations.create');
        Route::post('/space-registrations', [SpaceRegistrationController::class, 'store'])->name('space-registrations.store');
        Route::get('/space-registrations/{id}', [SpaceRegistrationController::class, 'show'])->name('space-registrations.show');
    });


 
   Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:admin']) 
        ->group(function () {
            Route::get('/listing-requests', [ListingRequestController::class, 'index'])->name('listing-requests.index');
            Route::get('/listing-requests/{registration}', [ListingRequestController::class, 'show'])->name('listing-requests.show');
            Route::post('/listing-requests/{registration}/approve', [ListingRequestController::class, 'approve'])->name('listing-requests.approve');
            Route::post('/listing-requests/{registration}/reject', [ListingRequestController::class, 'reject'])->name('listing-requests.reject');
        });

});




Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
