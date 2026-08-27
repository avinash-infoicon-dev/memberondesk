<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\SaasPayment;
use App\Models\SaasPlan;
use App\Models\SaasSubscription;
use App\Services\SaasBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaasSubscriptionController extends Controller
{
    public function index(): View
    {
        $subscriptions = SaasSubscription::query()
            ->with(['business', 'plan'])
            ->latest()
            ->paginate(20);

        return view('super-admin.saas-subscriptions.index', compact('subscriptions'));
    }

    public function create(): View
    {
        return view('super-admin.saas-subscriptions.create', [
            'businesses' => Business::query()->orderBy('name')->get(),
            'plans' => SaasPlan::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request, SaasBillingService $billing): RedirectResponse
    {
        $data = $request->validate([
            'business_id' => ['required', 'exists:businesses,id'],
            'saas_plan_id' => ['required', 'exists:saas_plans,id'],
            'method' => ['required', 'in:cash,upi,online,gateway'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $business = Business::query()->findOrFail($data['business_id']);
        $plan = SaasPlan::query()->findOrFail($data['saas_plan_id']);

        $billing->recordPayment(
            $business,
            $plan,
            (float) $plan->price,
            PaymentMethod::from($data['method']),
            $data['reference'] ?? null,
        );

        return redirect()->route('super-admin.saas-subscriptions.index')
            ->with('success', 'SaaS subscription activated and payment recorded.');
    }
}
