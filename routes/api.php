<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketReplyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/ping', function () {
        return response()->json([
            'message' => 'API is working',
        ]);
    });

    Route::get('/email/verify', function () {
        return response()->json([
            'message' => 'Your email address is not verified.',
        ], 403);
    })->name('api.verification.notice');

    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
            ->middleware('throttle:6,1')
            ->name('api.verification.send');

        Route::get('/profile', [ProfileController::class, 'show']);
        Route::post('/profile', [ProfileController::class, 'update']);

        Route::get('/departments', [DepartmentController::class, 'index']);

        Route::middleware('verified')->group(function () {
            Route::get('/tickets/{ticket}/replies', [TicketReplyController::class, 'index']);
            Route::post('/tickets/{ticket}/replies', [TicketReplyController::class, 'store']);

            Route::get('/tickets', [TicketController::class, 'index']);
            Route::post('/tickets', [TicketController::class, 'store']);
            Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
        });
    });
});
