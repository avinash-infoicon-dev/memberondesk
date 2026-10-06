<?php

namespace Tests\Feature;

use App\Enums\UserRole;
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

    public function test_api_login_issues_a_sanctum_token(): void
    {
        $user = User::factory()->create(['role' => UserRole::BusinessOwner]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'phpunit',
        ])->assertOk()->assertJsonStructure([
            'success',
            'message',
            'data' => ['token', 'user' => ['id', 'role']],
        ]);
    }
}
