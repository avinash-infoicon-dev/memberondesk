<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StorePaymentRequest;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Payment::class);

        $payments = Payment::query()->with('member')->latest()->paginate(20);

        return ApiResponse::success(PaymentResource::collection($payments), 'Data fetched successfully');
    }

    public function store(StorePaymentRequest $request): JsonResponse
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

        return ApiResponse::success(
            new PaymentResource($payment->load('member')),
            'Payment recorded successfully',
            201
        );
    }
}
