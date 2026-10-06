<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StorePaymentLinkRequest;
use App\Http\Resources\Api\V1\PaymentRequestResource;
use App\Http\Responses\ApiResponse;
use App\Models\Member;
use App\Models\PaymentRequest;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentRequestController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', PaymentRequest::class);

        $requests = PaymentRequest::query()->with('member')->latest()->paginate(20);

        return ApiResponse::success(PaymentRequestResource::collection($requests), 'Data fetched successfully');
    }

    public function store(StorePaymentLinkRequest $request): JsonResponse
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

        return ApiResponse::success(
            new PaymentRequestResource($paymentRequest->load('member')),
            'Payment request created successfully',
            201
        );
    }

    public function show(PaymentRequest $paymentRequest): JsonResponse
    {
        $this->authorize('view', $paymentRequest);

        return ApiResponse::success(
            new PaymentRequestResource($paymentRequest->load('member')),
            'Data fetched successfully'
        );
    }
}
