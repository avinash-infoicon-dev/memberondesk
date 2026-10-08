<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreMembershipPlanRequest;
use App\Http\Resources\Api\V1\MembershipPlanResource;
use App\Http\Responses\ApiResponse;
use App\Models\MembershipPlan;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', MembershipPlan::class);

        $plans = MembershipPlan::query()->where('is_active', true)->orderBy('name')->get();

        return ApiResponse::success(MembershipPlanResource::collection($plans), 'Data fetched successfully');
    }

    public function store(StoreMembershipPlanRequest $request): JsonResponse
    {
        $plan = MembershipPlan::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return ApiResponse::success(new MembershipPlanResource($plan), 'Plan created successfully', 201);
    }

    public function show(MembershipPlan $plan): JsonResponse
    {
        $this->authorize('view', $plan);

        return ApiResponse::success(new MembershipPlanResource($plan), 'Data fetched successfully');
    }

    public function destroy(MembershipPlan $plan): JsonResponse
    {
        $this->authorize('delete', $plan);

        $plan->delete();

        return ApiResponse::success(null, 'Plan deleted successfully');
    }
}
