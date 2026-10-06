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

        $attendance = isset($data['token'])
            ? $this->attendance->scan($data['token'], $request->user())
            : $this->attendance->checkIn(Member::query()->findOrFail($data['member_id']), scanner: $request->user());

        return ApiResponse::success(
            new AttendanceResource($attendance->load('member')),
            'Attendance recorded successfully',
            201
        );
    }
}
