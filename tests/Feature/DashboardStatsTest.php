<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Enums\MemberStatus;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_stats_count_each_member_once(): void
    {
        $owner = $this->gymOwner();
        app(TenantContext::class)->set($owner->business_id);
        $plan = MembershipPlan::query()->create([
            'business_id' => $owner->business_id,
            'name' => 'Monthly',
            'duration_days' => 30,
            'price' => 1000,
            'is_active' => true,
        ]);

        $active = $this->member($owner, 'Active Member', '9000000001');
        $this->paidSubscription($active, $plan, now()->toDateString(), now()->addDays(30)->toDateString());

        $expiring = $this->member($owner, 'Expiring Member', '9000000002');
        $this->paidSubscription($expiring, $plan, now()->subDays(20)->toDateString(), now()->addDays(3)->toDateString());

        $renewed = $this->member($owner, 'Renewed Member', '9000000003');
        $this->paidSubscription($renewed, $plan, now()->subDays(10)->toDateString(), now()->addDays(40)->toDateString());
        $this->paidSubscription($renewed, $plan, now()->subDays(2)->toDateString(), now()->addDays(2)->toDateString());

        $due = $this->member($owner, 'Due Member', '9000000004');
        app(SubscriptionService::class)->assign($due, $plan, [
            'activate' => true,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addDays(20)->toDateString(),
            'amount' => 1000,
        ]);

        $expired = $this->member($owner, 'Expired Member', '9000000005');
        $this->paidSubscription($expired, $plan, now()->subDays(40)->toDateString(), now()->subDay()->toDateString());

        $this->member($owner, 'New Member', '9000000006');

        Sanctum::actingAs($owner);

        $stats = $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->json('data.stats');

        $this->assertSame(6, $stats['total_members']);
        $this->assertSame(1, $stats['active_members']);
        $this->assertSame(2, $stats['expiring_soon']);
        $this->assertSame(1, $stats['payment_due']);
        $this->assertSame(1, $stats['expired_overdue']);
        $this->assertLessThanOrEqual(
            $stats['total_members'],
            $stats['active_members'] + $stats['expiring_soon'] + $stats['payment_due'] + $stats['expired_overdue']
        );

        $expiringNames = $this->getJson('/api/v1/dashboard')->json('data.expiring_members.*.name');
        $this->assertEqualsCanonicalizing(['Expiring Member', 'Renewed Member'], $expiringNames);
    }

    private function paidSubscription(Member $member, MembershipPlan $plan, string $startsAt, string $endsAt): void
    {
        $subscription = app(SubscriptionService::class)->assign($member, $plan, [
            'activate' => true,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'amount' => 1000,
        ]);
        $subscription->update(['paid_amount' => 1000]);
    }

    private function member(User $owner, string $name, string $phone): Member
    {
        return Member::query()->create([
            'business_id' => $owner->business_id,
            'member_code' => 'M'.substr($phone, -4),
            'name' => $name,
            'phone' => $phone,
            'status' => MemberStatus::Active,
            'joined_at' => now(),
        ]);
    }

    private function gymOwner(): User
    {
        app(TenantContext::class)->bypass();
        $owner = User::factory()->create(['role' => UserRole::BusinessOwner]);
        $business = Business::query()->create([
            'name' => 'Gym Desk',
            'type' => BusinessType::Gym,
            'owner_id' => $owner->id,
            'status' => BusinessStatus::Active,
            'activated_at' => now(),
        ]);
        $owner->update(['business_id' => $business->id]);
        app(TenantContext::class)->reset();

        return $owner->fresh();
    }
}
