<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SaasPayment;
use Illuminate\View\View;

class SaasPaymentController extends Controller
{
    public function index(): View
    {
        $payments = SaasPayment::query()
            ->with(['business', 'subscription.plan'])
            ->latest()
            ->paginate(20);

        return view('super-admin.saas-payments.index', compact('payments'));
    }
}
