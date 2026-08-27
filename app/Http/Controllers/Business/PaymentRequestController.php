<?php

namespace App\Http\Controllers\Business;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StorePaymentLinkRequest;
use App\Models\Member;
use App\Models\PaymentRequest;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentRequestController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
        $this->authorizeResource(PaymentRequest::class, 'payment_request');
    }

    public function index(): View
    {
        $requests = PaymentRequest::query()->with(['member', 'subscription'])->latest()->paginate(20);

        return view('business.payment-requests.index', compact('requests'));
    }

    public function create(): View
    {
        return view('business.payment-requests.create', [
            'members' => Member::query()->orderBy('name')->get(),
            'subscriptions' => Subscription::query()->with('member')->latest()->limit(100)->get(),
        ]);
    }

    public function store(StorePaymentLinkRequest $request): RedirectResponse
    {
        $member = Member::query()->findOrFail($request->integer('member_id'));
        $subscription = $request->filled('subscription_id')
            ? Subscription::query()->findOrFail($request->integer('subscription_id'))
            : null;

        $paymentRequest = $this->payments->createRequest(
            $member,
            (float) $request->input('amount'),
            $request->enum('method', PaymentMethod::class),
            $subscription,
        );

        return redirect()->route('business.payment-requests.show', $paymentRequest)
            ->with('success', 'Payment request created.');
    }

    public function show(PaymentRequest $paymentRequest): View
    {
        $paymentRequest->load(['member', 'subscription', 'payment', 'business']);

        return view('business.payment-requests.show', compact('paymentRequest'));
    }

    public function confirm(PaymentRequest $paymentRequest): RedirectResponse
    {
        $this->authorize('update', $paymentRequest);
        $this->payments->confirmRequest($paymentRequest, auth()->user());

        return back()->with('success', 'Payment confirmed and subscription updated.');
    }
}
