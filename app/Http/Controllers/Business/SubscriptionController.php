<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreSubscriptionRequest;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly PaymentService $payments,
    ) {
        $this->authorizeResource(Subscription::class, 'subscription');
    }

    public function index(Request $request): View
    {
        $subscriptions = Subscription::query()
            ->with(['member', 'plan'])
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('business.subscriptions.index', compact('subscriptions'));
    }

    public function create(Request $request): View
    {
        return view('business.subscriptions.create', [
            'members' => Member::query()->orderBy('name')->get(),
            'plans' => MembershipPlan::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedMember' => $request->integer('member_id') ?: null,
        ]);
    }

    public function store(StoreSubscriptionRequest $request): RedirectResponse
    {
        $member = Member::query()->findOrFail($request->integer('member_id'));
        $subscription = $this->subscriptions->assign($member, $request->plan(), $request->safe()->except(['member_id', 'membership_plan_id', 'collect_cash']));

        if ($request->boolean('collect_cash')) {
            $this->payments->recordCash($member, (float) $subscription->amount, $subscription, $request->user(), 'Collected on assignment');
            $subscription->refresh();
        }

        return redirect()->route('business.subscriptions.show', $subscription)
            ->with('success', 'Subscription created.');
    }

    public function show(Subscription $subscription): View
    {
        $subscription->load(['member', 'plan', 'payments']);

        return view('business.subscriptions.show', compact('subscription'));
    }

    public function cancel(Subscription $subscription): RedirectResponse
    {
        $this->authorize('update', $subscription);
        $this->subscriptions->cancel($subscription);

        return back()->with('success', 'Subscription cancelled.');
    }
}
