<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\Business;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\PaymentRequest;
use App\Models\SaasPlan;
use App\Models\Subscription;
use App\Policies\AttendancePolicy;
use App\Policies\BusinessPolicy;
use App\Policies\MemberPolicy;
use App\Policies\MembershipPlanPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PaymentRequestPolicy;
use App\Policies\SaasPlanPolicy;
use App\Policies\SubscriptionPolicy;
use App\Support\TenantContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, fn () => new TenantContext);
    }

    public function boot(): void
    {
        JsonResource::withoutWrapping();

        Gate::policy(Business::class, BusinessPolicy::class);
        Gate::policy(Member::class, MemberPolicy::class);
        Gate::policy(MembershipPlan::class, MembershipPlanPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(PaymentRequest::class, PaymentRequestPolicy::class);
        Gate::policy(SaasPlan::class, SaasPlanPolicy::class);

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('webhooks', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
