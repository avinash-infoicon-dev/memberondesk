<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class WebhookController extends Controller
{
    public function razorpay(Request $request, PaymentService $payments): JsonResponse
    {
        $secret = (string) config('payments.razorpay.webhook_secret');
        $signature = (string) $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();

        if ($secret === '') {
            throw new UnauthorizedHttpException('', 'Webhook is not configured.');
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expected, $signature)) {
            throw new UnauthorizedHttpException('', 'Invalid webhook signature.');
        }

        $event = $request->input('event');
        if ($event !== 'payment.captured') {
            return ApiResponse::success(['status' => 'ignored'], 'Webhook ignored');
        }

        $entity = $request->input('payload.payment.entity', []);
        $paymentId = $entity['id'] ?? null;
        $orderId = $entity['order_id'] ?? $entity['notes']['payment_request_token'] ?? null;
        $amountPaise = $entity['amount'] ?? null;

        if (! $paymentId || ! $orderId || $amountPaise === null) {
            throw ValidationException::withMessages(['payload' => 'Incomplete payment payload.']);
        }

        $payment = $payments->processGatewayPayment(
            'razorpay',
            (string) $paymentId,
            (string) $orderId,
            ((int) $amountPaise) / 100,
            $entity,
        );

        return ApiResponse::success([
            'status' => 'ok',
            'payment_id' => $payment->id,
        ], 'Payment verified successfully');
    }
}
