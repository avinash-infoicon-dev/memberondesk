<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreSaasPlanRequest;
use App\Http\Requests\SuperAdmin\UpdateSaasPlanRequest;
use App\Models\SaasPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SaasPlanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SaasPlan::class, 'saas_plan');
    }

    public function index(): View
    {
        $plans = SaasPlan::query()->orderBy('sort_order')->paginate(20);

        return view('super-admin.saas-plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('super-admin.saas-plans.create');
    }

    public function store(StoreSaasPlanRequest $request): RedirectResponse
    {
        $plan = SaasPlan::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
            'currency' => 'INR',
        ]);

        return redirect()->route('super-admin.saas-plans.index')
            ->with('success', "Plan {$plan->name} created.");
    }

    public function edit(SaasPlan $saasPlan): View
    {
        return view('super-admin.saas-plans.edit', ['plan' => $saasPlan]);
    }

    public function update(UpdateSaasPlanRequest $request, SaasPlan $saasPlan): RedirectResponse
    {
        $saasPlan->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('super-admin.saas-plans.index')
            ->with('success', 'Plan updated.');
    }

    public function destroy(SaasPlan $saasPlan): RedirectResponse
    {
        $saasPlan->delete();

        return redirect()->route('super-admin.saas-plans.index')
            ->with('success', 'Plan archived.');
    }
}
