<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login',    [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::get('/bookings', [\App\Http\Controllers\Api\BookingController::class, 'index'])->name('bookings.index');
        Route::post('/bookings', [\App\Http\Controllers\Api\BookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}', [\App\Http\Controllers\Api\BookingController::class, 'show'])->name('bookings.show');
        Route::patch('/bookings/{booking}/cancel', [\App\Http\Controllers\Api\BookingController::class, 'cancel'])->name('bookings.cancel');
    });
});

// Hotel and RoomType CRUD routes (public for now based on requirements, auth protection will be added later if instructed)
Route::apiResource('hotels', \App\Http\Controllers\Api\HotelController::class);
Route::apiResource('hotels.room-types', \App\Http\Controllers\Api\RoomTypeController::class)->scoped();

Route::get('/availability', [\App\Http\Controllers\Api\AvailabilityController::class, 'index'])->name('availability.index');

