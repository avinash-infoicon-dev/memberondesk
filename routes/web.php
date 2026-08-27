<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Business\AttendanceController;
use App\Http\Controllers\Business\DashboardController as BusinessDashboardController;
use App\Http\Controllers\Business\MemberController;
use App\Http\Controllers\Business\MembershipPlanController;
use App\Http\Controllers\Business\PaymentController;
use App\Http\Controllers\Business\PaymentRequestController;
use App\Http\Controllers\Business\ProfileController;
use App\Http\Controllers\Business\ReminderController;
use App\Http\Controllers\Business\ReportController as BusinessReportController;
use App\Http\Controllers\Business\SaasBillingController;
use App\Http\Controllers\Business\SubscriptionController;
use App\Http\Controllers\Public\PaymentPageController;
use App\Http\Controllers\SuperAdmin\AuditLogController;
use App\Http\Controllers\SuperAdmin\BusinessController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\ReportController as SuperAdminReportController;
use App\Http\Controllers\SuperAdmin\SaasPaymentController;
use App\Http\Controllers\SuperAdmin\SaasPlanController;
use App\Http\Controllers\SuperAdmin\SaasSubscriptionController;
use App\Http\Controllers\SuperAdmin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/pay/{token}', [PaymentPageController::class, 'show'])->name('public.pay.show');
Route::get('/pay/{token}/qr.png', [PaymentPageController::class, 'qr'])->name('public.pay.qr');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'active.user', 'tenant'])->group(function () {
    Route::middleware('super_admin')->prefix('admin')->name('super-admin.')->group(function () {
        Route::get('/', SuperAdminDashboardController::class)->name('dashboard');
        Route::resource('businesses', BusinessController::class);
        Route::resource('saas-plans', SaasPlanController::class)->except('show')->parameters(['saas-plans' => 'saas_plan']);
        Route::get('saas-subscriptions', [SaasSubscriptionController::class, 'index'])->name('saas-subscriptions.index');
        Route::get('saas-subscriptions/create', [SaasSubscriptionController::class, 'create'])->name('saas-subscriptions.create');
        Route::post('saas-subscriptions', [SaasSubscriptionController::class, 'store'])->name('saas-subscriptions.store');
        Route::get('saas-payments', [SaasPaymentController::class, 'index'])->name('saas-payments.index');
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        Route::get('reports', [SuperAdminReportController::class, 'index'])->name('reports.index');
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    Route::middleware('business_owner')->prefix('app')->name('business.')->group(function () {
        Route::get('/', BusinessDashboardController::class)->name('dashboard');
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('saas', [SaasBillingController::class, 'index'])->name('saas.index');
        Route::get('reports', [BusinessReportController::class, 'index'])->name('reports.index');

        Route::middleware('business.active')->group(function () {
            Route::resource('members', MemberController::class);
            Route::post('members/{member}/qr', [MemberController::class, 'regenerateQr'])->name('members.qr.regenerate');
            Route::get('members/{member}/qr.png', [MemberController::class, 'qrImage'])->name('members.qr.image');
            Route::resource('plans', MembershipPlanController::class)->except('show');
            Route::resource('subscriptions', SubscriptionController::class)->except(['edit', 'update']);
            Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
            Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
            Route::get('attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');
            Route::post('attendance/scan', [AttendanceController::class, 'storeScan'])->name('attendance.scan.store');
            Route::post('attendance/manual', [AttendanceController::class, 'storeManual'])->name('attendance.manual');
            Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store']);
            Route::resource('payment-requests', PaymentRequestController::class)->except(['edit', 'update', 'destroy']);
            Route::post('payment-requests/{payment_request}/confirm', [PaymentRequestController::class, 'confirm'])->name('payment-requests.confirm');
            Route::get('reminders', [ReminderController::class, 'index'])->name('reminders.index');
            Route::post('reminders', [ReminderController::class, 'store'])->name('reminders.store');
            Route::post('reminders/expiring', [ReminderController::class, 'expiring'])->name('reminders.expiring');
        });
    });
});
