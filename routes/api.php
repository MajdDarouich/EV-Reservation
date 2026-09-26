<?php

use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\ForgotPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::middleware('guest:api')->group(function () {
    Route::post('/register', [AuthenticationController::class, 'register']);
    Route::post('/resend-otp', [AuthenticationController::class, 'resendOtp']);
    Route::post('/verify-otp', [AuthenticationController::class, 'verifyOtp']);
    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthenticationController::class, 'logout']);
});
