<?php

namespace Tests\Feature;

use App\Enums\AttendanceSource;
use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\MemberService;
use App\Services\PaymentService;
use App\Services\QrCodeService;
use App\Services\SubscriptionService;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_payment_activates_subscription(): void
    {
        [$owner, $member, $plan] = $this->desk();

        app(TenantContext::class)->set($owner->business_id);
        $subscription = app(SubscriptionService::class)->assign($member, $plan);

        $this->assertSame(SubscriptionStatus::Pending, $subscription->status);

        $payment = app(PaymentService::class)->recordCash($member, (float) $plan->price, $subscription, $owner);

        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertSame(SubscriptionStatus::Active, $subscription->fresh()->status);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_qr_scan_records_attendance_for_active_members(): void
    {
        [$owner, $member, $plan] = $this->desk();
        app(TenantContext::class)->set($owner->business_id);

        $subscription = app(SubscriptionService::class)->assign($member, $plan, ['activate' => true]);
        $qr = app(QrCodeService::class)->generateFor($member);

        $attendance = app(AttendanceService::class)->scan($qr->token, $owner);

        $this->assertSame($member->id, $attendance->member_id);
        $this->assertSame($subscription->id, $attendance->subscription_id);
        $this->assertSame(AttendanceSource::Qr, $attendance->source);
    }

    public function test_invalid_qr_is_rejected(): void
    {
        [$owner] = $this->desk();
        app(TenantContext::class)->set($owner->business_id);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(AttendanceService::class)->scan('not-a-real-token', $owner);
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
            'upi_id' => 'gym@upi',
        ]);
        $owner->update(['business_id' => $business->id]);
        app(TenantContext::class)->set($business->id);

        $member = app(MemberService::class)->create([
            'business_id' => $business->id,
            'name' => 'Rahul',
            'phone' => '9888888888',
        ]);
        $plan = MembershipPlan::query()->create([
            'business_id' => $business->id,
            'name' => 'Monthly',
            'duration_days' => 30,
            'price' => 999,
            'is_active' => true,
        ]);

        return [$owner->fresh(), $member, $plan];
    }
}
