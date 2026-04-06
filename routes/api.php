<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\RoomTypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });
});

// Public read-only routes for hotels and room types
Route::apiResource('hotels', HotelController::class)->only(['index', 'show']);
Route::apiResource('hotels.room-types', RoomTypeController::class)->scoped()->only(['index', 'show']);

// Protected write routes for hotels and room types (admin operations)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('hotels', HotelController::class)->except(['index', 'show']);
    Route::apiResource('hotels.room-types', RoomTypeController::class)->scoped()->except(['index', 'show']);

    // Booking routes (correctly under /api/bookings, not /api/auth/bookings)
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [BookingController::class, 'store'])
        ->middleware('throttle:bookings')
        ->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
});

// Public availability search
Route::get('/availability', [AvailabilityController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('availability.index');
