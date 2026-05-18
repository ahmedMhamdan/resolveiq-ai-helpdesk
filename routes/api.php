<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\TicketReplyController;
use App\Http\Controllers\Api\ProfileController;

Route::prefix('v1')->group(function () {
    Route::get('/ping', function () {
        return response()->json([
            'message' => 'API is working',
        ]);
    });

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/profile', [ProfileController::class, 'show']);
        Route::post('/profile', [ProfileController::class, 'update']);

        Route::get('/departments', [DepartmentController::class, 'index']);

        Route::get('/tickets/{ticket}/replies', [TicketReplyController::class, 'index']);
        Route::post('/tickets/{ticket}/replies', [TicketReplyController::class, 'store']);

        Route::get('/tickets', [TicketController::class, 'index']);
        Route::post('/tickets', [TicketController::class, 'store']);
        Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
    });
});
