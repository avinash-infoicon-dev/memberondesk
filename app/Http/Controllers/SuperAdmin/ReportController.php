<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('super-admin.reports.index', [
            'recentPayments' => Payment::query()->with(['business', 'member'])->latest('paid_at')->limit(15)->get(),
            'expiring' => Subscription::query()->with(['business', 'member'])->expiring(7)->limit(15)->get(),
            'recentAttendance' => Attendance::query()->with(['business', 'member'])->latest('check_in_at')->limit(15)->get(),
        ]);
    }
}
