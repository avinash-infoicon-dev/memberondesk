<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_visible(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Sign in');
    }

    public function test_super_admin_is_sent_to_the_platform_dashboard(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('super-admin.dashboard'));
    }

    public function test_api_login_does_not_require_type(): void
    {
        $user = $this->ownerWithBusiness(BusinessType::Gym);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'android',
        ])->assertOk()->assertJsonPath('success', true)->assertJsonPath('data.type', 'gym')->assertJsonPath('data.user.type', 'gym')->assertJsonPath('data.business.type', 'gym');
    }

    public function test_api_login_returns_business_type_even_if_type_is_sent(): void
    {
        $user = $this->ownerWithBusiness(BusinessType::Library);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'type' => 'gym',
            'device_name' => 'phpunit',
        ])->assertOk()->assertJsonPath('data.type', 'library')->assertJsonPath('data.business.type', 'library');
    }

    public function test_tutor_owner_login_returns_tutor_type(): void
    {
        $user = $this->ownerWithBusiness(BusinessType::Tutor);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->assertJsonPath('data.type', 'tutor');
    }

    private function ownerWithBusiness(BusinessType $type): User
    {
        $owner = User::factory()->create(['role' => UserRole::BusinessOwner]);
        $business = Business::query()->create([
            'name' => $type->label().' Desk',
            'type' => $type,
            'owner_id' => $owner->id,
            'status' => BusinessStatus::Active,
            'activated_at' => now(),
        ]);
        $owner->update(['business_id' => $business->id]);

        return $owner->fresh();
    }
}
