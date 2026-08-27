<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StorePaymentLinkRequest;
use App\Http\Resources\Api\V1\PaymentRequestResource;
use App\Models\Member;
use App\Models\PaymentRequest;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentRequestController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PaymentRequest::class);

        return PaymentRequestResource::collection(
            PaymentRequest::query()->with('member')->latest()->paginate(20)
        );
    }

    public function store(StorePaymentLinkRequest $request): PaymentRequestResource
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

        return new PaymentRequestResource($paymentRequest->load('member'));
    }

    public function show(PaymentRequest $paymentRequest): PaymentRequestResource
    {
        $this->authorize('view', $paymentRequest);

        return new PaymentRequestResource($paymentRequest->load('member'));
    }
}
