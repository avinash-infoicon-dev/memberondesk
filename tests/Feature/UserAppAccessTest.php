<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserAppAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_control_the_access_flag_returned_by_dashboard(): void
    {
        $owner = $this->gymOwner();
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->assertTrue($owner->can_access_app);

        foreach ([false, true] as $allowed) {
            $this->actingAs($admin)->patch(route('super-admin.users.app-access', $owner), [
                'can_access_app' => $allowed,
            ])->assertRedirect();

            Sanctum::actingAs($owner->fresh());
            $this->getJson('/api/v1/dashboard')->assertOk()
                ->assertJsonPath('data.can_access_app', $allowed);
        }
    }

    public function test_business_user_cannot_change_app_access(): void
    {
        $owner = $this->gymOwner();
        $this->actingAs($owner)->patch(route('super-admin.users.app-access', $owner), [
            'can_access_app' => false,
        ])->assertForbidden();
        $this->assertTrue($owner->fresh()->can_access_app);
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
