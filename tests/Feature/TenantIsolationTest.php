<?php

namespace Tests\Feature;

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_business_cannot_see_another_business_members(): void
    {
        [$ownerA, $ownerB, $memberA] = $this->twoBusinesses();

        $this->actingAs($ownerB)
            ->get(route('business.members.index'))
            ->assertOk()
            ->assertDontSee($memberA->name);

        $this->actingAs($ownerB)
            ->get(route('business.members.show', $memberA))
            ->assertNotFound();

        $this->actingAs($ownerA)
            ->get(route('business.members.show', $memberA))
            ->assertOk()
            ->assertSee($memberA->name);
    }

    public function test_api_member_index_is_scoped_to_the_token_business(): void
    {
        [$ownerA, $ownerB, $memberA] = $this->twoBusinesses();

        $tokenB = $ownerB->createToken('android')->plainTextToken;

        $this->withToken($tokenB)
            ->getJson('/api/v1/members')
            ->assertOk()
            ->assertJsonMissing(['name' => $memberA->name]);

        $tokenA = $ownerA->createToken('android')->plainTextToken;

        $this->withToken($tokenA)
            ->getJson('/api/v1/members')
            ->assertOk()
            ->assertJsonFragment(['name' => $memberA->name]);
    }

    /**
     * @return array{0: User, 1: User, 2: Member}
     */
    private function twoBusinesses(): array
    {
        app(TenantContext::class)->bypass();

        $ownerA = User::factory()->create(['role' => UserRole::BusinessOwner]);
        $ownerB = User::factory()->create(['role' => UserRole::BusinessOwner]);

        $businessA = $this->makeBusiness($ownerA, 'Gym A');
        $businessB = $this->makeBusiness($ownerB, 'Library B');
        $ownerA->update(['business_id' => $businessA->id]);
        $ownerB->update(['business_id' => $businessB->id]);

        app(TenantContext::class)->set($businessA->id);
        $memberA = Member::query()->create([
            'business_id' => $businessA->id,
            'member_code' => 'M260001',
            'name' => 'Secret Member Alpha',
            'phone' => '9111111111',
            'status' => 'active',
            'joined_at' => now(),
        ]);
        app(TenantContext::class)->bypass();

        return [$ownerA->fresh(), $ownerB->fresh(), $memberA];
    }

    private function makeBusiness(User $owner, string $name): Business
    {
        return Business::query()->create([
            'name' => $name,
            'type' => BusinessType::Gym,
            'owner_id' => $owner->id,
            'status' => BusinessStatus::Active,
            'activated_at' => now(),
        ]);
    }
}
