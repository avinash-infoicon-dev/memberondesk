<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Attendance;
use App\Models\Business;
use App\Models\Member;
use App\Models\Payment;
use App\Models\SaasPayment;
use App\Models\SaasSubscription;
use App\Models\Subscription;
use App\Support\TenantContext;

class ReportService
{
    public function platformOverview(): array
    {
        app(TenantContext::class)->bypass();

        return [
            'businesses' => Business::query()->count(),
            'active_businesses' => Business::query()->where('status', 'active')->count(),
            'members' => Member::query()->count(),
            'active_subscriptions' => Subscription::query()->active()->count(),
            'expired_subscriptions' => Subscription::query()->expired()->count(),
            'member_revenue' => (float) Payment::query()->where('status', PaymentStatus::Paid)->sum('amount'),
            'saas_revenue' => (float) SaasPayment::query()->where('status', PaymentStatus::Paid)->sum('amount'),
            'saas_subscriptions' => SaasSubscription::query()->count(),
        ];
    }

    public function businessOverview(?int $businessId = null): array
    {
        $memberQuery = Member::query();
        $subscriptionQuery = Subscription::query();
        $attendanceQuery = Attendance::query();
        $paymentQuery = Payment::query()->where('status', PaymentStatus::Paid);

        return [
            'members' => $memberQuery->count(),
            'todays_attendance' => $attendanceQuery->whereDate('check_in_at', now())->count(),
            'expiring_members' => (clone $subscriptionQuery)->expiring(7)->count(),
            'expired_members' => (clone $subscriptionQuery)->expired()->count(),
            'pending_payments' => Subscription::query()
                ->whereIn('status', [SubscriptionStatus::Pending, SubscriptionStatus::Active])
                ->whereColumn('paid_amount', '<', 'amount')
                ->count(),
            'revenue' => (float) $paymentQuery->sum('amount'),
            'month_revenue' => (float) (clone $paymentQuery)->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
        ];
    }
}
