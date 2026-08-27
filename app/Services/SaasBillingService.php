<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SaasSubscriptionStatus;
use App\Models\Business;
use App\Models\SaasPayment;
use App\Models\SaasPlan;
use App\Models\SaasSubscription;
use Illuminate\Support\Facades\DB;

class SaasBillingService
{
    public function __construct(private readonly AuditService $audit) {}

    public function startTrial(Business $business, ?SaasPlan $plan = null): SaasSubscription
    {
        $plan ??= SaasPlan::query()->where('is_active', true)->orderBy('sort_order')->firstOrFail();

        $subscription = SaasSubscription::query()->create([
            'business_id' => $business->id,
            'saas_plan_id' => $plan->id,
            'status' => SaasSubscriptionStatus::Trial,
            'starts_at' => now(),
            'ends_at' => now()->addDays((int) config('saas.trial_days', 14)),
        ]);

        $this->audit->log('saas.trial_started', $subscription, new: $subscription->toArray());

        return $subscription;
    }

    public function subscribe(Business $business, SaasPlan $plan, SaasSubscriptionStatus $status = SaasSubscriptionStatus::Active): SaasSubscription
    {
        return SaasSubscription::query()->create([
            'business_id' => $business->id,
            'saas_plan_id' => $plan->id,
            'status' => $status,
            'starts_at' => now(),
            'ends_at' => now()->addDays($plan->interval->days()),
        ]);
    }

    public function recordPayment(
        Business $business,
        SaasPlan $plan,
        float $amount,
        PaymentMethod $method = PaymentMethod::Online,
        ?string $reference = null,
    ): SaasPayment {
        return DB::transaction(function () use ($business, $plan, $amount, $method, $reference) {
            $subscription = $this->subscribe($business, $plan);

            $payment = SaasPayment::query()->create([
                'business_id' => $business->id,
                'saas_subscription_id' => $subscription->id,
                'amount' => $amount,
                'currency' => $plan->currency,
                'method' => $method,
                'status' => PaymentStatus::Paid,
                'reference' => $reference,
                'paid_at' => now(),
            ]);

            $this->audit->log('saas.payment_recorded', $payment, new: $payment->toArray());

            return $payment->fresh(['subscription', 'business']);
        });
    }
}
