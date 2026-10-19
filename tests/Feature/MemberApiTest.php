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

class MemberApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_a_member_by_id(): void
    {
        $owner = $this->gymOwner();
        $member = Member::query()->create([
            'business_id' => $owner->business_id,
            'member_code' => 'M260004',
            'name' => 'Rahul Patil',
            'phone' => '9888888888',
            'status' => MemberStatus::Active,
            'joined_at' => now(),
        ]);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/members/'.$member->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $member->id)
            ->assertJsonPath('data.name', 'Rahul Patil')
            ->assertJsonPath('data.current_plan_name', null)
            ->assertJsonPath('data.active_subscription', null);
    }

    public function test_member_detail_includes_current_plan_name(): void
    {
        $owner = $this->gymOwner();
        app(TenantContext::class)->set($owner->business_id);

        $member = Member::query()->create([
            'business_id' => $owner->business_id,
            'member_code' => 'M260005',
            'name' => 'Sneha Shah',
            'phone' => '9777777777',
            'status' => MemberStatus::Active,
            'joined_at' => now(),
        ]);
        $plan = MembershipPlan::query()->create([
            'business_id' => $owner->business_id,
            'name' => 'Gold Monthly',
            'duration_days' => 30,
            'price' => 1499,
            'is_active' => true,
        ]);
        app(SubscriptionService::class)->assign($member, $plan, ['activate' => true]);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/members/'.$member->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.current_plan_name', 'Gold Monthly')
            ->assertJsonPath('data.active_subscription.plan_name', 'Gold Monthly')
            ->assertJsonPath('data.active_subscription.plan_id', $plan->id);
    }

    public function test_library_owner_cannot_view_a_gym_member(): void
    {
        $gymOwner = $this->gymOwner();
        $member = Member::query()->create([
            'business_id' => $gymOwner->business_id,
            'member_code' => 'M260004',
            'name' => 'Rahul Patil',
            'phone' => '9888888888',
            'status' => MemberStatus::Active,
            'joined_at' => now(),
        ]);

        Sanctum::actingAs($this->libraryOwner());

        $this->getJson('/api/v1/members/'.$member->id)
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    private function gymOwner(): User
    {
        return $this->ownerWithBusiness(BusinessType::Gym);
    }

    private function libraryOwner(): User
    {
        return $this->ownerWithBusiness(BusinessType::Library);
    }

    private function ownerWithBusiness(BusinessType $type): User
    {
        app(TenantContext::class)->bypass();
        $owner = User::factory()->create(['role' => UserRole::BusinessOwner]);
        $business = Business::query()->create([
            'name' => $type->label().' Desk',
            'type' => $type,
            'owner_id' => $owner->id,
            'status' => BusinessStatus::Active,
            'activated_at' => now(),
        ]);
        $owner->update(['business_id' => $business->id]);
        app(TenantContext::class)->reset();

        return $owner->fresh();
    }
}
