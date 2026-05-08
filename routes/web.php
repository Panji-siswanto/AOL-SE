<?php

use App\Http\Controllers\Admin\ListingRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Renter\SpaceRegistrationController;
use Illuminate\Support\Facades\Route;

// Owner
// Route::middleware(['auth:sanctum', 'permission:submit space registration'])->group(function () {
//     Route::post('/space-registrations', [SpaceRegistrationController::class, 'store']);
// });

// TEMPORARY FOR POSTMAN TESTING, set auth for later
Route::get('/space-registrations', [SpaceRegistrationController::class, 'index']); 
Route::post('/space-registrations', [SpaceRegistrationController::class, 'store']);
Route::get('/space-registrations/{id}', [SpaceRegistrationController::class, 'show']); 

Route::get('/admin/listing-requests', [ListingRequestController::class, 'index']);
Route::get('/admin/listing-requests/{registration}', [ListingRequestController::class, 'show']);
Route::post('/admin/listing-requests/{registration}/approve', [ListingRequestController::class, 'approve']);
Route::post('/admin/listing-requests/{registration}/reject', [ListingRequestController::class, 'reject']);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
