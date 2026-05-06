<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/verify-email', [EmailVerificationController::class, 'verify'])
        ->middleware('throttle:verify-email');
    Route::post('/resend-otp', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:resend-otp');
    Route::post('/forgot-password', [PasswordResetController::class, 'forgot'])
        ->middleware('throttle:forgot-password');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:reset-password');
});

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:registration');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware(['admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::post('/users/{user}/make-admin', [UserController::class, 'makeAdmin']);
        Route::post('/users/{user}/remove-admin', [UserController::class, 'removeAdmin']);
    });
});
