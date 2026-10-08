<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AttendanceResource;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Http\Resources\Api\V1\SubscriptionResource;
use App\Http\Responses\ApiResponse;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(ReportService $reports): JsonResponse
    {
        $user = request()->user();

        return ApiResponse::success([
            'stats' => $reports->businessOverview(),
            'business' => $user?->business?->only(['id', 'name', 'type', 'status', 'upi_id']),
            'today_attendance' => AttendanceResource::collection(
                Attendance::query()->with('member')->whereDate('check_in_at', now())->latest('check_in_at')->limit(8)->get()
            ),
            'expiring' => SubscriptionResource::collection(
                Subscription::query()->with(['member', 'plan'])->expiring(7)->limit(8)->get()
            ),
            'pending' => SubscriptionResource::collection(
                Subscription::query()
                    ->with(['member', 'plan'])
                    ->whereIn('status', ['pending', 'active'])
                    ->whereColumn('paid_amount', '<', 'amount')
                    ->latest()
                    ->limit(8)
                    ->get()
            ),
            'recent_payments' => PaymentResource::collection(
                Payment::query()->with('member')->latest('paid_at')->limit(8)->get()
            ),
        ], 'Data fetched successfully');
    }
}
