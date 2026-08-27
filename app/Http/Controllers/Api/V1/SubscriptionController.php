<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreSubscriptionRequest;
use App\Http\Resources\Api\V1\SubscriptionResource;
use App\Models\Member;
use App\Models\Subscription;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly PaymentService $payments,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Subscription::class);

        return SubscriptionResource::collection(
            Subscription::query()->with(['member', 'plan'])->latest()->paginate(20)
        );
    }

    public function store(StoreSubscriptionRequest $request): SubscriptionResource
    {
        $member = Member::query()->findOrFail($request->integer('member_id'));
        $subscription = $this->subscriptions->assign(
            $member,
            $request->plan(),
            $request->safe()->except(['member_id', 'membership_plan_id', 'collect_cash'])
        );

        if ($request->boolean('collect_cash')) {
            $this->payments->recordCash($member, (float) $subscription->amount, $subscription, $request->user());
            $subscription->refresh();
        }

        return new SubscriptionResource($subscription->load(['member', 'plan']));
    }

    public function show(Subscription $subscription): SubscriptionResource
    {
        $this->authorize('view', $subscription);

        return new SubscriptionResource($subscription->load(['member', 'plan']));
    }
}
