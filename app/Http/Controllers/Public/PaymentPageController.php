<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PaymentPageController extends Controller
{
    public function show(string $token): View
    {
        $paymentRequest = PaymentRequest::withoutGlobalScopes()
            ->with(['member', 'business', 'subscription'])
            ->where('token', $token)
            ->firstOrFail();

        return view('public.pay', compact('paymentRequest'));
    }

    public function qr(string $token): Response
    {
        $paymentRequest = PaymentRequest::withoutGlobalScopes()->where('token', $token)->firstOrFail();

        $builder = new Builder(
            writer: new PngWriter(),
            data: $paymentRequest->upiLink(),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 320,
            margin: 10,
        );

        return response($builder->build()->getString(), 200, [
            'Content-Type' => 'image/png',
        ]);
    }
}
