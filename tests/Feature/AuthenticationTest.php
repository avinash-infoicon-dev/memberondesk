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

    public function test_api_login_requires_type(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'owner@example.com',
            'password' => 'password',
        ])->assertUnprocessable()->assertJsonPath('success', false);
    }

    public function test_api_login_issues_a_sanctum_token_for_matching_type(): void
    {
        $user = $this->ownerWithBusiness(BusinessType::Gym);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'type' => 'gym',
            'device_name' => 'phpunit',
        ])->assertOk()->assertJsonPath('success', true)->assertJsonPath('data.type', 'gym')->assertJsonPath('data.user.type', 'gym')->assertJsonPath('data.business.type', 'gym')->assertJsonStructure([
            'success',
            'message',
            'data' => ['token', 'type', 'user' => ['id', 'role', 'type'], 'business' => ['id', 'type']],
        ]);
    }

    public function test_gym_owner_cannot_login_as_library(): void
    {
        $user = $this->ownerWithBusiness(BusinessType::Gym);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'type' => 'library',
        ])->assertUnprocessable()->assertJsonPath('success', false)->assertJsonPath('data.type.0', 'This account does not belong to a library.');
    }

    public function test_library_owner_cannot_login_as_gym(): void
    {
        $user = $this->ownerWithBusiness(BusinessType::Library);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'type' => 'gym',
        ])->assertUnprocessable()->assertJsonPath('success', false);
    }

    public function test_combined_business_can_login_as_gym_or_library(): void
    {
        $user = $this->ownerWithBusiness(BusinessType::Both);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'type' => 'gym',
        ])->assertOk()->assertJsonPath('data.type', 'gym');

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'type' => 'library',
        ])->assertOk()->assertJsonPath('data.type', 'library');
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
