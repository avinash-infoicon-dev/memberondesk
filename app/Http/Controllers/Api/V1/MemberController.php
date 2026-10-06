<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreMemberRequest;
use App\Http\Requests\Business\UpdateMemberRequest;
use App\Http\Resources\Api\V1\MemberResource;
use App\Http\Responses\ApiResponse;
use App\Models\Member;
use App\Services\MemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(private readonly MemberService $members)
    {
        $this->authorizeResource(Member::class, 'member');
    }

    public function index(Request $request): JsonResponse
    {
        $members = Member::query()
            ->with(['activeQrCode', 'activeSubscription'])
            ->when($request->string('q')->toString(), function ($query, $q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('member_code', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(20);

        return ApiResponse::success(MemberResource::collection($members), 'Data fetched successfully');
    }

    public function store(StoreMemberRequest $request): JsonResponse
    {
        $member = $this->members->create($request->validated())->load('activeQrCode');

        return ApiResponse::success(new MemberResource($member), 'Member created successfully', 201);
    }

    public function show(Member $member): JsonResponse
    {
        return ApiResponse::success(
            new MemberResource($member->load(['activeQrCode', 'activeSubscription.plan'])),
            'Data fetched successfully'
        );
    }

    public function update(UpdateMemberRequest $request, Member $member): JsonResponse
    {
        return ApiResponse::success(
            new MemberResource($this->members->update($member, $request->validated())),
            'Member updated successfully'
        );
    }
}
