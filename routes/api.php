<?php

use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\MemberController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PaymentRequestController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/webhooks/razorpay', [WebhookController::class, 'razorpay'])->middleware('throttle:webhooks');

    Route::middleware(['auth:sanctum', 'active.user', 'tenant'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::middleware('business_owner')->group(function () {
            Route::get('dashboard', DashboardController::class);
            Route::put('/profile', [ProfileController::class, 'update']);
            Route::patch('/profile', [ProfileController::class, 'update']);
            Route::apiResource('members', MemberController::class);
            Route::apiResource('plans', PlanController::class)->only(['index', 'store', 'show', 'destroy']);
            Route::apiResource('subscriptions', SubscriptionController::class)->only(['index', 'store', 'show']);
            Route::get('attendance', [AttendanceController::class, 'index']);
            Route::post('attendance', [AttendanceController::class, 'store']);
            Route::apiResource('payments', PaymentController::class)->only(['index', 'store']);
            Route::apiResource('payment-requests', PaymentRequestController::class)->only(['index', 'store', 'show']);
        });
    });
});
