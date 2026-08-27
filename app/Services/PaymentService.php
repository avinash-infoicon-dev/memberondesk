<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentRequestStatus;
use App\Enums\PaymentStatus;
use App\Models\Member;
use App\Models\Payment;
use App\Models\PaymentRequest;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly AuditService $audit,
    ) {}

    public function recordCash(
        Member $member,
        float $amount,
        ?Subscription $subscription = null,
        ?User $recorder = null,
        ?string $notes = null,
        ?string $reference = null,
    ): Payment {
        return $this->recordManual($member, $amount, PaymentMethod::Cash, $subscription, $recorder, $notes, $reference);
    }

    public function recordManual(
        Member $member,
        float $amount,
        PaymentMethod $method,
        ?Subscription $subscription = null,
        ?User $recorder = null,
        ?string $notes = null,
        ?string $reference = null,
    ): Payment {
        return $this->settle($member, $amount, $method, $subscription, $recorder, $notes, $reference);
    }

    public function createRequest(
        Member $member,
        float $amount,
        PaymentMethod $method = PaymentMethod::Upi,
        ?Subscription $subscription = null,
        ?string $upiId = null,
    ): PaymentRequest {
        $business = $member->business;

        return DB::transaction(function () use ($member, $amount, $method, $subscription, $upiId, $business) {
            $request = PaymentRequest::query()->create([
                'business_id' => $member->business_id,
                'member_id' => $member->id,
                'subscription_id' => $subscription?->id,
                'amount' => $amount,
                'status' => PaymentRequestStatus::Pending,
                'token' => Str::lower(Str::ulid()->toBase32()),
                'method' => $method,
                'upi_id' => $upiId ?: $business?->upi_id,
                'expires_at' => now()->addHours((int) config('payments.payment_request_ttl_hours', 48)),
            ]);

            $this->audit->log('payment_request.created', $request, new: $request->toArray());

            return $request;
        });
    }

    public function confirmRequest(PaymentRequest $request, ?User $recorder = null, ?string $gatewayPaymentId = null): Payment
    {
        if ($request->status === PaymentRequestStatus::Paid) {
            $existing = $request->payment;
            if ($existing) {
                return $existing;
            }
        }

        if (! $request->isOpen() && $request->status !== PaymentRequestStatus::Paid) {
            throw ValidationException::withMessages([
                'payment_request' => 'This payment request is no longer payable.',
            ]);
        }

        return DB::transaction(function () use ($request, $recorder, $gatewayPaymentId) {
            $locked = PaymentRequest::query()->whereKey($request->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === PaymentRequestStatus::Paid && $locked->payment) {
                return $locked->payment;
            }

            $payment = $this->settle(
                $locked->member,
                (float) $locked->amount,
                $locked->method,
                $locked->subscription,
                $recorder,
                'Payment request '.$locked->token,
                $locked->token,
                $locked,
                $gatewayPaymentId,
            );

            $locked->update([
                'status' => PaymentRequestStatus::Paid,
                'paid_at' => now(),
                'gateway_order_id' => $locked->gateway_order_id,
            ]);

            return $payment;
        });
    }

    public function processGatewayPayment(
        string $gateway,
        string $gatewayPaymentId,
        string $orderOrToken,
        float $amount,
        array $payload = [],
    ): Payment {
        if ($existing = Payment::query()->where('gateway_payment_id', $gatewayPaymentId)->first()) {
            return $existing;
        }

        $request = PaymentRequest::withoutGlobalScopes()
            ->where(function ($query) use ($orderOrToken) {
                $query->where('token', $orderOrToken)
                    ->orWhere('gateway_order_id', $orderOrToken);
            })
            ->first();

        if (! $request) {
            throw ValidationException::withMessages([
                'payment' => 'Matching payment request not found.',
            ]);
        }

        if (round((float) $request->amount, 2) !== round($amount, 2)) {
            throw ValidationException::withMessages([
                'amount' => 'Paid amount does not match the payment request.',
            ]);
        }

        $request->gateway = $gateway;
        $request->meta = array_merge($request->meta ?? [], ['webhook' => $payload]);
        $request->save();

        return $this->confirmRequest($request, null, $gatewayPaymentId);
    }

    private function settle(
        Member $member,
        float $amount,
        PaymentMethod $method,
        ?Subscription $subscription = null,
        ?User $recorder = null,
        ?string $notes = null,
        ?string $reference = null,
        ?PaymentRequest $paymentRequest = null,
        ?string $gatewayPaymentId = null,
    ): Payment {
        return DB::transaction(function () use (
            $member,
            $amount,
            $method,
            $subscription,
            $recorder,
            $notes,
            $reference,
            $paymentRequest,
            $gatewayPaymentId,
        ) {
            if ($gatewayPaymentId) {
                $existing = Payment::query()->where('gateway_payment_id', $gatewayPaymentId)->first();
                if ($existing) {
                    return $existing;
                }
            }

            $payment = Payment::query()->create([
                'business_id' => $member->business_id,
                'member_id' => $member->id,
                'subscription_id' => $subscription?->id,
                'payment_request_id' => $paymentRequest?->id,
                'amount' => $amount,
                'method' => $method,
                'status' => PaymentStatus::Paid,
                'reference' => $reference,
                'gateway' => $gatewayPaymentId ? config('payments.default_gateway') : null,
                'gateway_payment_id' => $gatewayPaymentId,
                'paid_at' => now(),
                'recorded_by' => $recorder?->id,
                'notes' => $notes,
            ]);

            if ($subscription) {
                $this->subscriptions->applyPayment($subscription, $amount);
            }

            $this->audit->log('payment.recorded', $payment, new: $payment->toArray());
            event(new \App\Events\PaymentRecorded($payment));

            return $payment->fresh(['member', 'subscription']);
        });
    }
}
