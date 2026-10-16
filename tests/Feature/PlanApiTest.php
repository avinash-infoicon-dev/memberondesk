<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\MembershipPlan;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlanApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_and_partially_update_their_plan(): void
    {
        $owner = $this->gymOwner();
        $plan = $this->makePlan($owner);
        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/plans/'.$plan->id)
            ->assertOk()
            ->assertJsonPath('data.id', $plan->id);

        foreach (['putJson', 'patchJson'] as $method) {
            $this->{$method}('/api/v1/plans/'.$plan->id, ['price' => 750])
                ->assertOk()
                ->assertJsonPath('success', true);
        }

        $this->assertDatabaseHas('membership_plans', [
            'id' => $plan->id,
            'name' => 'Monthly',
            'price' => 750,
        ]);

        $this->patchJson('/api/v1/plans/'.$plan->id, ['duration_days' => 0])
            ->assertUnprocessable();
    }

    public function test_owner_cannot_view_or_update_another_business_plan(): void
    {
        $plan = $this->makePlan($this->gymOwner());
        Sanctum::actingAs($this->libraryOwner());

        $this->getJson('/api/v1/plans/'.$plan->id)->assertNotFound();
        $this->patchJson('/api/v1/plans/'.$plan->id, ['price' => 750])->assertNotFound();
        $this->assertDatabaseHas('membership_plans', ['id' => $plan->id, 'price' => 500]);
    }

    private function makePlan(User $owner): MembershipPlan
    {
        return MembershipPlan::query()->create([
            'business_id' => $owner->business_id,
            'name' => 'Monthly',
            'duration_days' => 30,
            'price' => 500,
            'is_active' => true,
        ]);
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
