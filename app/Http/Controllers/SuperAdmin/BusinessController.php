<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\BusinessStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreBusinessRequest;
use App\Http\Requests\SuperAdmin\UpdateBusinessRequest;
use App\Models\Business;
use App\Models\User;
use App\Services\AuditService;
use App\Services\SaasBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function __construct(
        private readonly SaasBillingService $billing,
        private readonly AuditService $audit,
    ) {
        $this->authorizeResource(Business::class, 'business');
    }

    public function index(): View
    {
        $businesses = Business::query()
            ->with(['owner', 'currentSaasSubscription.plan'])
            ->latest()
            ->paginate(20);

        return view('super-admin.businesses.index', compact('businesses'));
    }

    public function create(): View
    {
        return view('super-admin.businesses.create');
    }

    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        $business = DB::transaction(function () use ($request) {
            $owner = User::query()->create([
                'name' => $request->string('owner_name'),
                'email' => $request->string('owner_email'),
                'phone' => $request->input('owner_phone'),
                'password' => $request->string('owner_password'),
                'role' => UserRole::BusinessOwner,
                'is_active' => true,
            ]);

            $business = Business::query()->create([
                ...$request->safe()->only([
                    'name', 'type', 'email', 'phone', 'upi_id', 'address', 'city', 'state', 'pincode', 'status',
                ]),
                'owner_id' => $owner->id,
                'activated_at' => $request->enum('status', BusinessStatus::class) === BusinessStatus::Active ? now() : null,
            ]);

            $owner->update(['business_id' => $business->id]);
            $this->billing->startTrial($business);
            $this->audit->log('business.created', $business, new: $business->toArray());

            return $business;
        });

        return redirect()->route('super-admin.businesses.show', $business)
            ->with('success', 'Business created and trial started.');
    }

    public function show(Business $business): View
    {
        $business->load(['owner', 'currentSaasSubscription.plan', 'saasPayments']);

        return view('super-admin.businesses.show', compact('business'));
    }

    public function edit(Business $business): View
    {
        return view('super-admin.businesses.edit', compact('business'));
    }

    public function update(UpdateBusinessRequest $request, Business $business): RedirectResponse
    {
        $status = $request->enum('status', BusinessStatus::class);

        $business->update([
            ...$request->safe()->only([
                'name', 'type', 'email', 'phone', 'upi_id', 'address', 'city', 'state', 'pincode', 'status',
            ]),
            'whatsapp_enabled' => $request->boolean('whatsapp_enabled'),
            'activated_at' => $status === BusinessStatus::Active ? ($business->activated_at ?? now()) : $business->activated_at,
            'suspended_at' => $status === BusinessStatus::Suspended ? now() : null,
        ]);

        $this->audit->log('business.updated', $business);

        return redirect()->route('super-admin.businesses.show', $business)
            ->with('success', 'Business updated.');
    }

    public function destroy(Business $business): RedirectResponse
    {
        $business->delete();
        $this->audit->log('business.deleted', $business);

        return redirect()->route('super-admin.businesses.index')
            ->with('success', 'Business archived.');
    }
}
