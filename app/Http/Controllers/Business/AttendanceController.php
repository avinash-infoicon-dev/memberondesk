<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Member;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendance)
    {
        $this->authorizeResource(Attendance::class, 'attendance');
    }

    public function index(Request $request): View
    {
        $records = Attendance::query()
            ->with(['member', 'scanner'])
            ->when($request->date('date'), fn ($q, $date) => $q->whereDate('check_in_at', $date))
            ->latest('check_in_at')
            ->paginate(30)
            ->withQueryString();

        return view('business.attendance.index', compact('records'));
    }

    public function scan(): View
    {
        $this->authorize('create', Attendance::class);

        return view('business.attendance.scan');
    }

    public function storeScan(Request $request): JsonResponse
    {
        $this->authorize('create', Attendance::class);
        $data = $request->validate(['token' => ['required', 'string']]);

        $attendance = $this->attendance->scan($data['token'], $request->user());

        return response()->json([
            'message' => $attendance->check_out_at ? 'Checked out' : 'Checked in',
            'attendance' => [
                'id' => $attendance->id,
                'member' => $attendance->member?->name,
                'code' => $attendance->member?->member_code,
                'check_in_at' => $attendance->check_in_at?->toDateTimeString(),
                'check_out_at' => $attendance->check_out_at?->toDateTimeString(),
                'ends_at' => $attendance->subscription?->ends_at?->toDateString(),
            ],
        ]);
    }

    public function storeManual(Request $request): RedirectResponse
    {
        $this->authorize('create', Attendance::class);
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
        ]);

        $member = Member::query()->findOrFail($data['member_id']);
        $this->attendance->checkIn($member, scanner: $request->user());

        return back()->with('success', 'Attendance recorded.');
    }
}
