<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreMemberRequest;
use App\Http\Requests\Business\UpdateMemberRequest;
use App\Http\Resources\Api\V1\MemberResource;
use App\Models\Member;
use App\Services\MemberService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MemberController extends Controller
{
    public function __construct(private readonly MemberService $members)
    {
        $this->authorizeResource(Member::class, 'member');
    }

    public function index(Request $request): AnonymousResourceCollection
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

        return MemberResource::collection($members);
    }

    public function store(StoreMemberRequest $request): MemberResource
    {
        return new MemberResource($this->members->create($request->validated())->load('activeQrCode'));
    }

    public function show(Member $member): MemberResource
    {
        return new MemberResource($member->load(['activeQrCode', 'activeSubscription.plan']));
    }

    public function update(UpdateMemberRequest $request, Member $member): MemberResource
    {
        return new MemberResource($this->members->update($member, $request->validated()));
    }
}
