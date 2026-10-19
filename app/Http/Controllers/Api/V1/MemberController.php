<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MembershipDisplayStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreMemberRequest;
use App\Http\Requests\Business\UpdateMemberRequest;
use App\Http\Resources\Api\V1\MemberResource;
use App\Http\Responses\ApiResponse;
use App\Models\Member;
use App\Services\MemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function __construct(private readonly MemberService $members)
    {
        $this->authorizeResource(Member::class, 'member');
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::enum(MembershipDisplayStatus::class)],
        ]);

        $members = Member::query()
            ->with(['activeQrCode', 'activeSubscription'])
            ->when($request->string('q')->toString(), function ($query, $q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('member_code', 'like', "%{$q}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->displayStatus($request->string('status')->toString()))
            ->latest()
            ->paginate(20);

        return ApiResponse::success(MemberResource::collection($members), 'Data fetched successfully');
    }

    public function store(StoreMemberRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['profile_image']);
        $data = $this->applyProfileImage($request->file('profile_image'), $data, $request->user()->business_id);

        $member = $this->members->create($data)->load('activeQrCode');

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
        $data = $request->safe()->except(['profile_image']);
        $oldPhoto = $member->photo_path;
        $data = $this->applyProfileImage(
            $request->file('profile_image'),
            $data,
            $request->user()->business_id,
            $member->id
        );

        $member = $this->members->update($member, $data)->load(['activeQrCode', 'activeSubscription']);

        if (
            $request->hasFile('profile_image')
            && $oldPhoto
            && $oldPhoto !== $member->photo_path
        ) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return ApiResponse::success(
            new MemberResource($member),
            'Member updated successfully'
        );
    }

    public function destroy(Member $member): JsonResponse
    {
        $photo = $member->photo_path;
        $this->members->delete($member);

        if ($photo) {
            Storage::disk('public')->delete($photo);
        }

        return ApiResponse::success(null, 'Member deleted successfully');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyProfileImage(
        ?UploadedFile $file,
        array $data,
        ?int $businessId,
        ?int $memberId = null,
    ): array {
        if (! $file) {
            return $data;
        }

        $folder = 'member-images/'.($businessId ?: 'shared');
        if ($memberId) {
            $folder .= '/'.$memberId;
        }

        $data['photo_path'] = $file->store($folder, 'public');

        return $data;
    }
}
