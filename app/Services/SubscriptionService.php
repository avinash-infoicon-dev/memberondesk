<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubscriptionService
{
    public function __construct(private readonly AuditService $audit) {}

    public function assign(Member $member, MembershipPlan $plan, array $data = []): Subscription
    {
        if ((int) $member->business_id !== (int) $plan->business_id) {
            throw new InvalidArgumentException('Plan does not belong to this business.');
        }

        $startsAt = $data['starts_at'] ?? now()->toDateString();
        $endsAt = $data['ends_at'] ?? \Illuminate\Support\Carbon::parse($startsAt)->addDays($plan->duration_days)->toDateString();
        $amount = $data['amount'] ?? $plan->price;
        $markActive = (bool) ($data['activate'] ?? false);

        return DB::transaction(function () use ($member, $plan, $startsAt, $endsAt, $amount, $markActive, $data) {
            $subscription = Subscription::query()->create([
                'business_id' => $member->business_id,
                'member_id' => $member->id,
                'membership_plan_id' => $plan->id,
                'status' => $markActive ? SubscriptionStatus::Active : SubscriptionStatus::Pending,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'amount' => $amount,
                'paid_amount' => 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->audit->log('subscription.created', $subscription, new: $subscription->toArray());

            return $subscription->fresh(['member', 'plan']);
        });
    }

    public function applyPayment(Subscription $subscription, float $amount): Subscription
    {
        $subscription->paid_amount = round((float) $subscription->paid_amount + $amount, 2);

        if ($subscription->isPaidInFull() && $subscription->status !== SubscriptionStatus::Cancelled) {
            $subscription->status = SubscriptionStatus::Active;
        }

        $subscription->save();

        return $subscription;
    }

    public function activate(Subscription $subscription): Subscription
    {
        $subscription->update(['status' => SubscriptionStatus::Active]);
        $this->audit->log('subscription.activated', $subscription);

        return $subscription;
    }

    public function cancel(Subscription $subscription, ?string $notes = null): Subscription
    {
        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
            'notes' => $notes ?: $subscription->notes,
        ]);
        $this->audit->log('subscription.cancelled', $subscription);

        return $subscription;
    }

    public function expireOverdue(): int
    {
        return Subscription::withoutGlobalScopes()
            ->where('status', SubscriptionStatus::Active)
            ->whereDate('ends_at', '<', now())
            ->update(['status' => SubscriptionStatus::Expired]);
    }
}
