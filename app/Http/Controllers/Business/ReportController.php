<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('business.reports.index', [
            'expiring' => Subscription::query()->with('member')->expiring(7)->get(),
            'expired' => Subscription::query()->with('member')->expired()->latest('ends_at')->limit(50)->get(),
            'pending' => Subscription::query()->with('member')->get()->filter(fn (Subscription $s) => $s->balance() > 0),
            'payments' => Payment::query()->with('member')->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])->latest('paid_at')->get(),
            'attendance' => Attendance::query()->with('member')->whereDate('check_in_at', now())->latest('check_in_at')->get(),
        ]);
    }
}
