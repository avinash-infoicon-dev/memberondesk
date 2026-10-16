<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_change_and_clear_the_app_lock_key(): void
    {
        config(['mobile.app_lock_key' => 'fallback']);
        $this->actingAs(User::factory()->create(['role' => UserRole::SuperAdmin]));
        $this->get(route('super-admin.settings.edit'))->assertOk();
        $this->put(route('super-admin.settings.update'), ['app_lock_key' => 'release-2'])
            ->assertRedirect(route('super-admin.settings.edit'));
        $this->assertSame('release-2', AppSetting::appLockKey());
        $this->put(route('super-admin.settings.update'), ['app_lock_key' => ''])->assertRedirect();
        $this->assertSame('', AppSetting::appLockKey());
    }

    public function test_business_owner_cannot_read_or_change_app_settings(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::BusinessOwner]));
        $this->get(route('super-admin.settings.edit'))->assertForbidden();
        $this->put(route('super-admin.settings.update'), ['app_lock_key' => 'changed'])->assertForbidden();
        $this->assertDatabaseCount('app_settings', 0);
    }
}
