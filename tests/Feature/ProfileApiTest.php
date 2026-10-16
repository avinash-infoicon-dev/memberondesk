<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_profile_text_with_post(): void
    {
        $owner = $this->gymOwner();
        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/profile', [
            'name' => 'Updated Owner',
            'business_name' => 'Updated Gym',
        ])->assertOk()
            ->assertJsonPath('data.user.name', 'Updated Owner')
            ->assertJsonPath('data.business.name', 'Updated Gym');

        $this->assertSame('Updated Owner', $owner->fresh()->name);
        $this->assertSame('Updated Gym', $owner->fresh()->business->name);
    }

    public function test_owner_can_save_text_and_image_together_with_post(): void
    {
        Storage::fake('public');
        $owner = $this->gymOwner();
        Sanctum::actingAs($owner);

        $this->post('/api/v1/profile', [
            'name' => 'Owner With Image',
            'profile_image' => UploadedFile::fake()->image('avatar.jpg'),
        ], ['Accept' => 'application/json'])->assertOk()
            ->assertJsonPath('data.user.name', 'Owner With Image');

        $this->assertSame('Owner With Image', $owner->fresh()->name);
        Storage::disk('public')->assertExists($owner->fresh()->profile_image_path);
    }

    public function test_owner_can_upload_and_replace_a_profile_image(): void
    {
        Storage::fake('public');
        $owner = $this->gymOwner();
        Sanctum::actingAs($owner);

        $this->post('/api/v1/profile', [
            '_method' => 'PATCH',
            'profile_image' => UploadedFile::fake()->image('avatar.jpg'),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.user.profile_image_url', fn ($url) => is_string($url));

        $oldPath = $owner->fresh()->profile_image_path;
        Storage::disk('public')->assertExists($oldPath);

        $this->patchJson('/api/v1/profile', ['name' => 'Updated Owner'])->assertOk();
        $this->assertSame($oldPath, $owner->fresh()->profile_image_path);

        $this->post('/api/v1/profile', [
            '_method' => 'PUT',
            'profile_image' => UploadedFile::fake()->image('replacement.png'),
        ], ['Accept' => 'application/json'])->assertOk();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($owner->fresh()->profile_image_path);
        $this->getJson('/api/v1/auth/me')->assertOk()
            ->assertJsonPath('data.user.profile_image_url', $owner->fresh()->profile_image_url);
    }

    public function test_invalid_profile_images_are_rejected(): void
    {
        Storage::fake('public');
        $owner = $this->gymOwner();
        Sanctum::actingAs($owner);

        foreach ([UploadedFile::fake()->create('file.txt', 1, 'text/plain'),
            UploadedFile::fake()->image('large.jpg')->size(2049)] as $file) {
            $this->post('/api/v1/profile', [
                '_method' => 'PATCH',
                'profile_image' => $file,
            ], ['Accept' => 'application/json'])->assertUnprocessable();
        }

        $this->assertNull($owner->fresh()->profile_image_path);
        $this->assertSame([], Storage::disk('public')->allFiles());
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
