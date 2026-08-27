<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\ReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ReportService $reports): View
    {
        return view('business.dashboard', [
            'stats' => $reports->businessOverview(),
            'todayAttendance' => Attendance::query()->with('member')->whereDate('check_in_at', now())->latest('check_in_at')->limit(8)->get(),
            'expiring' => Subscription::query()->with('member')->expiring(7)->limit(8)->get(),
            'pending' => Subscription::query()->with('member')->get()->filter(fn (Subscription $s) => $s->balance() > 0)->take(8),
            'recentPayments' => Payment::query()->with('member')->latest('paid_at')->limit(8)->get(),
            'business' => auth()->user()->business,
        ]);
    }
}
