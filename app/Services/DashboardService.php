<?php

namespace App\Services;

use App\Enums\MemberStatus;
use App\Enums\NotificationStatus;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DashboardService
{
    public function payload(User $user): array
    {
        $business = $user->business;
        $business?->loadMissing('owner');
        $todayAttendanceCount = (int) Attendance::query()
            ->whereDate('check_in_at', now())
            ->distinct()
            ->count('member_id');

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $monthCollection = (float) Payment::query()
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [$monthStart, $monthEnd])
            ->sum('amount');

        return [
            'business' => [
                'owner_id' => $business?->owner_id,
                'owner_name' => $business?->owner?->name ?? $user->name,
                'business_name' => $business?->name,
                'business_type' => strtoupper((string) $business?->type?->value),
                'logo_url' => $this->publicUrl($business?->logo_path),
                'notification_count' => NotificationLog::query()
                    ->where('status', NotificationStatus::Queued)
                    ->count(),
            ],
            'collection' => [
                'period' => 'current_month',
                'period_label' => 'Current Month',
                'currency' => 'INR',
                'currency_symbol' => '₹',
                'total_collection' => $monthCollection,
                'formatted_collection' => $this->formatMoney($monthCollection),
            ],
            'stats' => [
                'active_members' => Member::query()
                    ->where('status', MemberStatus::Active)
                    ->whereHas('subscriptions', fn ($query) => $query->active())
                    ->count(),
                'present_today' => $todayAttendanceCount,
                'total_members' => Member::query()->count(),
                'today_attendance' => $todayAttendanceCount,
                'expiring_soon' => Subscription::query()->expiring(7)->count(),
                'expired_overdue' => Subscription::query()->expired()->count(),
                'payment_due' => Subscription::query()
                    ->whereIn('status', [SubscriptionStatus::Pending, SubscriptionStatus::Active])
                    ->whereColumn('paid_amount', '<', 'amount')
                    ->count(),
            ],
            'expiring_members' => $this->expiringMembers(),
            'today_attendance' => $this->todayAttendance(),
            'payments_log' => $this->paymentsLog(),
        ];
    }

    private function expiringMembers(): array
    {
        return Subscription::query()
            ->with(['member', 'plan'])
            ->expiring(7)
            ->orderBy('ends_at')
            ->limit(10)
            ->get()
            ->map(function (Subscription $subscription) {
                $member = $subscription->member;
                $plan = $subscription->plan;
                $amountDue = $subscription->balance();
                $daysRemaining = (int) now()->startOfDay()->diffInDays($subscription->ends_at?->startOfDay() ?? now(), false);

                return [
                    'member_id' => $member?->id,
                    'member_code' => $member?->member_code,
                    'name' => $member?->name,
                    'initials' => $this->initials($member?->name),
                    'profile_image_url' => $this->publicUrl($member?->photo_path),
                    'plan' => [
                        'plan_id' => $plan?->id,
                        'plan_name' => $plan?->name,
                        'duration' => $this->durationLabel($plan?->duration_days),
                        'price' => (float) ($plan?->price ?? 0),
                    ],
                    'membership_status' => 'expiring_soon',
                    'expiry_date' => $subscription->ends_at?->toDateString(),
                    'days_remaining' => max(0, $daysRemaining),
                    'amount_due' => $amountDue,
                    'currency' => 'INR',
                    'formatted_amount_due' => $this->formatMoney($amountDue),
                    'can_renew' => true,
                    'phone' => $member?->phone,
                ];
            })
            ->values()
            ->all();
    }

    private function todayAttendance(): array
    {
        return Attendance::query()
            ->with('member')
            ->whereDate('check_in_at', now())
            ->latest('check_in_at')
            ->limit(20)
            ->get()
            ->map(function (Attendance $attendance) {
                $member = $attendance->member;
                $checkIn = $attendance->check_in_at;

                return [
                    'attendance_id' => $attendance->id,
                    'member_id' => $member?->id,
                    'member_code' => $member?->member_code,
                    'name' => $member?->name,
                    'initials' => $this->initials($member?->name),
                    'profile_image_url' => $this->publicUrl($member?->photo_path),
                    'attendance_status' => 'present',
                    'check_in_at' => $checkIn?->toIso8601String(),
                    'check_in_time' => $checkIn?->timezone(config('app.timezone'))->format('h:i A'),
                ];
            })
            ->values()
            ->all();
    }

    private function paymentsLog(): array
    {
        return Payment::query()
            ->with(['member', 'subscription.plan'])
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(function (Payment $payment) {
                $member = $payment->member;
                $planName = $payment->subscription?->plan?->name;
                $paidAt = $payment->paid_at;
                $amount = (float) $payment->amount;

                return [
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->reference
                        ?: $payment->gateway_payment_id
                        ?: 'TXN-'.$payment->id,
                    'member_id' => $member?->id,
                    'member_code' => $member?->member_code,
                    'member_name' => $member?->name,
                    'member_initials' => $this->initials($member?->name),
                    'plan_name' => $planName,
                    'amount' => $amount,
                    'currency' => 'INR',
                    'formatted_amount' => $this->formatMoney($amount),
                    'payment_method' => $payment->method?->label(),
                    'payment_status' => $payment->status?->value,
                    'payment_date' => $paidAt?->toIso8601String(),
                    'formatted_payment_date' => $paidAt?->timezone(config('app.timezone'))->format('d M Y, h:i A'),
                    'description' => $payment->notes ?: 'Membership payment',
                ];
            })
            ->values()
            ->all();
    }

    private function initials(?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $letters = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)));

        return $letters->implode('') ?: null;
    }

    private function durationLabel(?int $days): ?string
    {
        if (! $days) {
            return null;
        }

        if ($days >= 365 && $days % 365 === 0) {
            $years = intdiv($days, 365);

            return $years.' '.($years === 1 ? 'Year' : 'Years');
        }

        if ($days >= 30 && $days % 30 === 0) {
            $months = intdiv($days, 30);

            return $months.' '.($months === 1 ? 'Month' : 'Months');
        }

        return $days.' '.($days === 1 ? 'Day' : 'Days');
    }

    private function formatMoney(float $amount): string
    {
        return '₹'.number_format($amount, $amount == floor($amount) ? 0 : 2);
    }

    private function publicUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
