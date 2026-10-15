<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AttendanceResource;
use App\Http\Responses\ApiResponse;
use App\Models\Attendance;
use App\Models\Member;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendance) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Attendance::class);

        $records = Attendance::query()
            ->with('member')
            ->when($request->date('date'), fn ($q, $date) => $q->whereDate('check_in_at', $date))
            ->latest('check_in_at')
            ->paginate(30);

        return ApiResponse::success(AttendanceResource::collection($records), 'Data fetched successfully');
    }

     
    public function store(Request $request): JsonResponse
{
    $this->authorize('create', Attendance::class);

    $data = $request->validate([
        'token' => ['required_without:member_id', 'string'],
        'member_id' => ['required_without:token', 'integer'],
    ]);

    // Find the member using token or member_id
    $member = isset($data['token'])
        ? Member::query()->where('token', $data['token'])->firstOrFail()
        : Member::query()->findOrFail($data['member_id']);

    // Prevent duplicate attendance for the same member on the same date
    $alreadyCheckedIn = Attendance::query()
        ->where('member_id', $member->id)
        ->whereDate('created_at', today())
        ->exists();

    if ($alreadyCheckedIn) {
        return ApiResponse::success(
            null,
            'Attendance already recorded for today.',
            200
        );
    }

    // Record attendance
    $attendance = isset($data['token'])
        ? $this->attendance->scan($data['token'], $request->user())
        : $this->attendance->checkIn(
            $member,
            scanner: $request->user()
        );

    $attendance->load(['member.activeSubscription', 'subscription']);

    $hasActiveMembership = (bool) $attendance->member?->activeSubscription;

    return ApiResponse::success(
        new AttendanceResource($attendance),
        $hasActiveMembership
            ? 'Attendance recorded successfully'
            : 'Attendance recorded. No active membership found.',
        201
    );
}
}
