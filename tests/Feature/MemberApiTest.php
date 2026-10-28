<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Enums\MemberStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            ->assertJsonPath('data.last_active_plan_name', null)
            ->assertJsonPath('data.active_subscription', null)
            ->assertJsonPath('data.last_active_subscription', null);
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
            ->assertJsonPath('data.last_active_plan_name', null)
            ->assertJsonPath('data.active_subscription.plan_name', 'Gold Monthly')
            ->assertJsonPath('data.active_subscription.plan_id', $plan->id)
            ->assertJsonPath('data.last_active_subscription', null);
    }

    public function test_member_detail_includes_last_active_plan_when_expired(): void
    {
        $owner = $this->gymOwner();
        app(TenantContext::class)->set($owner->business_id);

        $member = Member::query()->create([
            'business_id' => $owner->business_id,
            'member_code' => 'M260006',
            'name' => 'Amit Rao',
            'phone' => '9666666666',
            'status' => MemberStatus::Active,
            'joined_at' => now()->subMonths(2),
        ]);
        $plan = MembershipPlan::query()->create([
            'business_id' => $owner->business_id,
            'name' => 'Silver Quarterly',
            'duration_days' => 90,
            'price' => 2999,
            'is_active' => true,
        ]);
        $subscription = app(SubscriptionService::class)->assign($member, $plan, [
            'activate' => true,
            'starts_at' => now()->subMonths(2)->toDateString(),
            'ends_at' => now()->subDay()->toDateString(),
        ]);
        $subscription->update(['status' => SubscriptionStatus::Expired]);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/members/'.$member->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.active_subscription', null)
            ->assertJsonPath('data.current_plan_name', 'Silver Quarterly')
            ->assertJsonPath('data.last_active_plan_name', null)
            ->assertJsonPath('data.last_active_subscription', null);
    }

    public function test_member_detail_prefers_latest_assigned_active_plan(): void
    {
        $owner = $this->gymOwner();
        app(TenantContext::class)->set($owner->business_id);

        $member = Member::query()->create([
            'business_id' => $owner->business_id,
            'member_code' => 'M260007',
            'name' => 'Neha Verma',
            'phone' => '9555555555',
            'status' => MemberStatus::Active,
            'joined_at' => now(),
        ]);
        $basic = MembershipPlan::query()->create([
            'business_id' => $owner->business_id,
            'name' => 'Basic',
            'duration_days' => 30,
            'price' => 499,
            'is_active' => true,
        ]);
        $silver = MembershipPlan::query()->create([
            'business_id' => $owner->business_id,
            'name' => 'Silver',
            'duration_days' => 30,
            'price' => 999,
            'is_active' => true,
        ]);

        $previous = app(SubscriptionService::class)->assign($member, $basic, [
            'activate' => true,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addDays(30)->toDateString(),
        ]);
        $latest = app(SubscriptionService::class)->assign($member, $silver, [
            'activate' => true,
            'starts_at' => now()->addMonths(3)->toDateString(),
            'ends_at' => now()->addMonths(4)->toDateString(),
        ]);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/members/'.$member->id)
            ->assertOk()
            ->assertJsonPath('data.current_plan_name', 'Silver')
            ->assertJsonPath('data.active_subscription.plan_name', 'Silver')
            ->assertJsonPath('data.active_subscription.id', $latest->id)
            ->assertJsonPath('data.last_active_plan_name', 'Basic')
            ->assertJsonPath('data.last_active_subscription.plan_name', 'Basic')
            ->assertJsonPath('data.last_active_subscription.id', $previous->id);
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

    public function test_owner_can_update_member_via_multipart_post_without_phone(): void
    {
        Storage::fake('public');

        $owner = $this->gymOwner();
        $member = Member::query()->create([
            'business_id' => $owner->business_id,
            'member_code' => 'M260008',
            'name' => 'Old Name',
            'phone' => '9444444444',
            'status' => MemberStatus::Active,
            'joined_at' => now(),
        ]);

        Sanctum::actingAs($owner);

        // Prefer POST for multipart; PUT also works via ParseMultipartFormData middleware.
        // Phone is optional (device-local privacy mode).
        $this->post('/api/v1/members/'.$member->id, [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '9444444444',
            'address' => 'Pune',
            'status' => MemberStatus::Active->value,
            'profileImage' => UploadedFile::fake()->image('member.jpg'),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.email', 'updated@example.com');

        $this->assertNotNull($member->fresh()->photo_path);
        Storage::disk('public')->assertExists($member->fresh()->photo_path);
    }

    public function test_member_profile_tracks_the_first_whatsapp_welcome_only(): void
    {
        $owner = $this->gymOwner();
        $member = Member::query()->create([
            'business_id' => $owner->business_id,
            'member_code' => 'M260009',
            'name' => 'Asha Kale',
            'phone' => '9333333333',
            'status' => MemberStatus::Active,
            'joined_at' => now(),
        ]);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/members/'.$member->id)
            ->assertOk()
            ->assertJsonPath('data.whatsapp_welcome_sent', false)
            ->assertJsonPath('data.whatsapp_welcome_message', null);

        $this->postJson('/api/v1/members/'.$member->id.'/whatsapp-welcome', [
            'message' => 'Welcome to the gym, Asha.',
        ])->assertOk()
            ->assertJsonPath('data.whatsapp_welcome_sent', true)
            ->assertJsonPath('data.whatsapp_welcome_message', 'Welcome to the gym, Asha.');

        $this->assertNotNull($member->fresh()->whatsapp_welcome_sent_at);

        $this->getJson('/api/v1/members/'.$member->id)
            ->assertOk()
            ->assertJsonPath('data.whatsapp_welcome_sent', true)
            ->assertJsonPath('data.whatsapp_welcome_message', 'Welcome to the gym, Asha.');
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
