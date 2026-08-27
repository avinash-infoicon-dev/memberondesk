<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        $logs = AuditLog::query()->with(['user', 'business'])->latest()->paginate(30);

        return view('super-admin.audit-logs.index', compact('logs'));
    }
}
