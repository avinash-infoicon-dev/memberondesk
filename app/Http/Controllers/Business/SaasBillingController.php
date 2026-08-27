<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\SaasPlan;
use App\Models\SaasSubscription;
use Illuminate\View\View;

class SaasBillingController extends Controller
{
    public function index(): View
    {
        $business = auth()->user()->business;

        return view('business.saas.index', [
            'business' => $business,
            'subscription' => $business?->currentSaasSubscription?->load('plan'),
            'plans' => SaasPlan::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'payments' => $business?->saasPayments()->latest()->limit(20)->get() ?? collect(),
        ]);
    }
}
