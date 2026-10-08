<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreSubscriptionRequest;
use App\Http\Resources\Api\V1\SubscriptionResource;
use App\Http\Responses\ApiResponse;
use App\Models\Member;
use App\Models\Subscription;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly PaymentService $payments,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Subscription::class);

        $subscriptions = Subscription::query()
            ->with(['member', 'plan'])
            ->when($request->integer('member_id'), fn ($query, $memberId) => $query->where('member_id', $memberId))
            ->latest()
            ->paginate(20);

        return ApiResponse::success(SubscriptionResource::collection($subscriptions), 'Data fetched successfully');
    }

    public function store(StoreSubscriptionRequest $request): JsonResponse
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

        return ApiResponse::success(
            new SubscriptionResource($subscription->load(['member', 'plan'])),
            'Subscription created successfully',
            201
        );
    }

    public function show(Subscription $subscription): JsonResponse
    {
        $this->authorize('view', $subscription);

        return ApiResponse::success(
            new SubscriptionResource($subscription->load(['member', 'plan'])),
            'Data fetched successfully'
        );
    }
}
