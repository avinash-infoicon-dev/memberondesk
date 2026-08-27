<?php

namespace App\Http\Controllers\Business;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StorePaymentRequest;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
        $this->authorizeResource(Payment::class, 'payment');
    }

    public function index(): View
    {
        $payments = Payment::query()->with(['member', 'subscription'])->latest()->paginate(20);

        return view('business.payments.index', compact('payments'));
    }

    public function create(): View
    {
        return view('business.payments.create', [
            'members' => Member::query()->orderBy('name')->get(),
            'subscriptions' => Subscription::query()->with('member')->latest()->limit(100)->get(),
        ]);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $member = Member::query()->findOrFail($request->integer('member_id'));
        $subscription = $request->filled('subscription_id')
            ? Subscription::query()->findOrFail($request->integer('subscription_id'))
            : $member->subscriptions()->latest('id')->first();

        $this->payments->recordManual(
            $member,
            (float) $request->input('amount'),
            $request->enum('method', PaymentMethod::class),
            $subscription,
            $request->user(),
            $request->input('notes'),
            $request->input('reference'),
        );

        return redirect()->route('business.payments.index')->with('success', 'Payment recorded.');
    }
}
