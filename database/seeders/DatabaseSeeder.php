<?php

namespace Database\Seeders;

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Enums\PaymentMethod;
use App\Enums\SaasPlanInterval;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\MembershipPlan;
use App\Models\SaasPlan;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\MemberService;
use App\Services\PaymentService;
use App\Services\SaasBillingService;
use App\Services\SubscriptionService;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(TenantContext::class)->bypass();

        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'leo.a@example.org',
            'phone' => '9999999999',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
        ]);

        $monthly = SaasPlan::query()->create([
            'name' => 'Monthly',
            'slug' => 'monthly',
            'interval' => SaasPlanInterval::Monthly,
            'price' => 499,
            'currency' => 'INR',
            'max_members' => 500,
            'features' => ['QR attendance', 'WhatsApp reminders', 'Payments'],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        SaasPlan::query()->create([
            'name' => 'Yearly',
            'slug' => 'yearly',
            'interval' => SaasPlanInterval::Yearly,
            'price' => 4999,
            'currency' => 'INR',
            'max_members' => 2000,
            'features' => ['QR attendance', 'WhatsApp reminders', 'Payments', 'Priority support'],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $gym = $this->seedBusiness(
            'Iron Temple Gym',
            'zoe.m@example.net',
            'Rahul Sharma',
            BusinessType::Gym,
            'gym@irontemple.test',
            '9876543210',
            'rahul@upi',
        );

        $library = $this->seedBusiness(
            'Quiet Stack Library',
            'yosef.c@example.com',
            'Anita Desai',
            BusinessType::Library,
            'desk@quietstack.test',
            '9876501234',
            'anita@upi',
        );

        app(SaasBillingService::class)->startTrial($gym, $monthly);
        app(SaasBillingService::class)->startTrial($library, $monthly);

        $this->seedGymDesk($gym);
        $this->seedLibraryDesk($library);

        $admin->forceFill(['email_verified_at' => now()])->save();
    }

    private function seedBusiness(
        string $name,
        string $ownerEmail,
        string $ownerName,
        BusinessType $type,
        string $email,
        string $phone,
        string $upi,
    ): Business {
        $owner = User::query()->create([
            'name' => $ownerName,
            'email' => $ownerEmail,
            'phone' => $phone,
            'password' => Hash::make('password'),
            'role' => UserRole::BusinessOwner,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $business = Business::query()->create([
            'name' => $name,
            'type' => $type,
            'owner_id' => $owner->id,
            'email' => $email,
            'phone' => $phone,
            'upi_id' => $upi,
            'address' => '12 Market Road',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'status' => BusinessStatus::Active,
            'activated_at' => now(),
            'whatsapp_enabled' => true,
        ]);

        $owner->update(['business_id' => $business->id]);

        return $business;
    }

    private function seedGymDesk(Business $business): void
    {
        app(TenantContext::class)->set($business->id);

        $monthly = MembershipPlan::query()->create([
            'business_id' => $business->id,
            'name' => 'Monthly Gym',
            'description' => 'Full gym floor access',
            'duration_days' => 30,
            'price' => 1500,
            'is_active' => true,
        ]);

        $quarterly = MembershipPlan::query()->create([
            'business_id' => $business->id,
            'name' => 'Quarterly Gym',
            'duration_days' => 90,
            'price' => 4000,
            'is_active' => true,
        ]);

        $members = app(MemberService::class);
        $rahul = $members->create([
            'business_id' => $business->id,
            'name' => 'Rahul Patil',
            'phone' => '9000000001',
            'email' => 'rahul.member@example.com',
            'gender' => 'male',
        ]);

        $priya = $members->create([
            'business_id' => $business->id,
            'name' => 'Priya Kulkarni',
            'phone' => '9000000002',
            'email' => 'priya.member@example.com',
            'gender' => 'female',
        ]);

        $subscriptions = app(SubscriptionService::class);
        $payments = app(PaymentService::class);
        $attendance = app(AttendanceService::class);

        $sub = $subscriptions->assign($rahul, $monthly);
        $payments->recordCash($rahul, (float) $sub->amount, $sub->fresh(), $business->owner, 'Demo cash');
        $attendance->checkIn($rahul->fresh(), scanner: $business->owner);

        $expiring = $subscriptions->assign($priya, $quarterly, [
            'starts_at' => now()->subDays(83)->toDateString(),
        ]);
        $payments->recordCash($priya, 2000, $expiring, $business->owner, 'Partial');

        app(TenantContext::class)->bypass();
    }

    private function seedLibraryDesk(Business $business): void
    {
        app(TenantContext::class)->set($business->id);

        MembershipPlan::query()->create([
            'business_id' => $business->id,
            'name' => 'Reading Pass',
            'duration_days' => 30,
            'price' => 400,
            'is_active' => true,
        ]);

        app(MemberService::class)->create([
            'business_id' => $business->id,
            'name' => 'Aarav Joshi',
            'phone' => '9000000101',
        ]);

        app(TenantContext::class)->bypass();
    }
}
