<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreMembershipPlanRequest;
use App\Http\Requests\Business\UpdateMembershipPlanRequest;
use App\Models\MembershipPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MembershipPlanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(MembershipPlan::class, 'plan');
    }

    public function index(): View
    {
        $plans = MembershipPlan::query()->latest()->paginate(20);

        return view('business.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('business.plans.create');
    }

    public function store(StoreMembershipPlanRequest $request): RedirectResponse
    {
        MembershipPlan::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('business.plans.index')->with('success', 'Plan created.');
    }

    public function edit(MembershipPlan $plan): View
    {
        return view('business.plans.edit', compact('plan'));
    }

    public function update(UpdateMembershipPlanRequest $request, MembershipPlan $plan): RedirectResponse
    {
        $plan->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('business.plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(MembershipPlan $plan): RedirectResponse
    {
        $plan->delete();

        return redirect()->route('business.plans.index')->with('success', 'Plan archived.');
    }
}
