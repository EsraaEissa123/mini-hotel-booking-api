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
    });
});

// Hotel and RoomType CRUD routes (public for now based on requirements, auth protection will be added later if instructed)
Route::apiResource('hotels', \App\Http\Controllers\Api\HotelController::class);
Route::apiResource('hotels.room-types', \App\Http\Controllers\Api\RoomTypeController::class)->scoped();
