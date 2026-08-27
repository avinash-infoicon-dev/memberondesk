<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Enums\PaymentRequestStatus;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\Member;
use App\Models\User;
use App\Services\PaymentService;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_rejects_invalid_signatures(): void
    {
        config(['payments.razorpay.webhook_secret' => 'whsec_test']);

        $this->postJson('/api/v1/webhooks/razorpay', ['event' => 'payment.captured'], [
            'X-Razorpay-Signature' => 'bad',
        ])->assertUnauthorized();
    }

    public function test_valid_webhook_marks_the_request_paid_once(): void
    {
        config(['payments.razorpay.webhook_secret' => 'whsec_test']);

        app(TenantContext::class)->bypass();
        $owner = User::factory()->create(['role' => UserRole::BusinessOwner]);
        $business = Business::query()->create([
            'name' => 'Pay Gym',
            'type' => BusinessType::Gym,
            'owner_id' => $owner->id,
            'status' => BusinessStatus::Active,
            'upi_id' => 'pay@upi',
        ]);
        $owner->update(['business_id' => $business->id]);
        app(TenantContext::class)->set($business->id);

        $member = Member::query()->create([
            'business_id' => $business->id,
            'member_code' => 'M260099',
            'name' => 'Payee',
            'phone' => '9000090000',
            'status' => 'active',
        ]);

        $request = app(PaymentService::class)->createRequest($member, 250.00);

        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_test_1',
                        'order_id' => $request->token,
                        'amount' => 25000,
                    ],
                ],
            ],
        ];

        $raw = json_encode($payload);
        $signature = hash_hmac('sha256', $raw, 'whsec_test');

        $this->call('POST', '/api/v1/webhooks/razorpay', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
        ], $raw)->assertOk()->assertJsonPath('status', 'ok');

        $this->assertSame(PaymentRequestStatus::Paid, $request->fresh()->status);
        $this->assertDatabaseCount('payments', 1);

        $this->call('POST', '/api/v1/webhooks/razorpay', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
        ], $raw)->assertOk();

        $this->assertDatabaseCount('payments', 1);
    }
}
