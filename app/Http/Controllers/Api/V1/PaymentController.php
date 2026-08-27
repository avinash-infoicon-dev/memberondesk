<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StorePaymentRequest;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Payment::class);

        return PaymentResource::collection(
            Payment::query()->with('member')->latest()->paginate(20)
        );
    }

    public function store(StorePaymentRequest $request): PaymentResource
    {
        $member = Member::query()->findOrFail($request->integer('member_id'));
        $subscription = $request->filled('subscription_id')
            ? Subscription::query()->findOrFail($request->integer('subscription_id'))
            : null;

        $payment = $this->payments->recordManual(
            $member,
            (float) $request->input('amount'),
            $request->enum('method', PaymentMethod::class),
            $subscription,
            $request->user(),
            $request->input('notes'),
            $request->input('reference'),
        );

        return new PaymentResource($payment->load('member'));
    }
}
