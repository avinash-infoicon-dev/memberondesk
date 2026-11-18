<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionPaymentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_records_one_payment_with_the_given_method(): void
    {
        [$owner, $member, $plan] = $this->desk();
        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/subscriptions', [
            'member_id' => $member->id,
            'membership_plan_id' => $plan->id,
            'starts_at' => '2026-11-18',
            'notes' => 'Initial membership',
            'collect_cash' => true,
            'method' => 'upi',
            'amount' => 1500,
            'reference' => 'INV-99051',
            'payment_notes' => 'Initial registration fee',
        ])->assertCreated()
            ->assertJsonPath('data.paid_amount', 1500)
            ->assertJsonPath('data.status', 'active');

        $this->assertSame(1, Payment::query()->count());
        $payment = Payment::query()->first();
        $this->assertSame('upi', $payment->method->value);
        $this->assertSame('INV-99051', $payment->reference);
        $this->assertSame('Initial registration fee', $payment->notes);

        $this->postJson('/api/v1/payments', [
            'member_id' => $member->id,
            'amount' => 1500,
            'method' => 'cash',
            'subscription_id' => $payment->subscription_id,
        ])->assertMethodNotAllowed();

        $this->getJson('/api/v1/payments?member_id='.$member->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.method', 'upi');
    }

    public function test_collect_cash_without_a_method_records_one_cash_payment(): void
    {
        [$owner, $member, $plan] = $this->desk();
        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/subscriptions', [
            'member_id' => $member->id,
            'membership_plan_id' => $plan->id,
            'collect_cash' => true,
        ])->assertCreated()
            ->assertJsonPath('data.paid_amount', 999);

        $this->assertSame(1, Payment::query()->count());
        $this->assertSame('cash', Payment::query()->first()->method->value);
    }

    /**
     * @return array{0: User, 1: Member, 2: MembershipPlan}
     */
    private function desk(): array
    {
        app(TenantContext::class)->bypass();
        $owner = User::factory()->create(['role' => UserRole::BusinessOwner]);
        $business = Business::query()->create([
            'name' => 'Flow Gym',
            'type' => BusinessType::Gym,
            'owner_id' => $owner->id,
            'status' => BusinessStatus::Active,
            'activated_at' => now(),
        ]);
        $owner->update(['business_id' => $business->id]);
        app(TenantContext::class)->reset();

        app(TenantContext::class)->set($business->id);
        $member = Member::query()->create([
            'business_id' => $business->id,
            'member_code' => 'M260051',
            'name' => 'test6',
            'phone' => '9888888888',
            'status' => 'active',
            'joined_at' => now(),
        ]);
        $plan = MembershipPlan::query()->create([
            'business_id' => $business->id,
            'name' => 'ttt',
            'duration_days' => 30,
            'price' => 999,
            'is_active' => true,
        ]);
        app(TenantContext::class)->reset();

        return [$owner->fresh(), $member, $plan];
    }
}
