<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreMembershipPlanRequest;
use App\Http\Resources\Api\V1\MembershipPlanResource;
use App\Models\MembershipPlan;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlanController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', MembershipPlan::class);

        return MembershipPlanResource::collection(
            MembershipPlan::query()->where('is_active', true)->orderBy('name')->get()
        );
    }

    public function store(StoreMembershipPlanRequest $request): MembershipPlanResource
    {
        $plan = MembershipPlan::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return new MembershipPlanResource($plan);
    }

    public function show(MembershipPlan $plan): MembershipPlanResource
    {
        $this->authorize('view', $plan);

        return new MembershipPlanResource($plan);
    }
}
