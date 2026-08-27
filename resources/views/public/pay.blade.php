<x-guest-layout title="Pay membership">
    <div class="guest-form" style="min-height:100vh">
        <div class="auth-card" style="text-align:center">
            <p class="muted">{{ $paymentRequest->business?->name }}</p>
            <h2>{{ format_inr($paymentRequest->amount) }}</h2>
            <p>{{ $paymentRequest->member?->name }}</p>
            <p><span class="badge {{ status_badge_class($paymentRequest->status->color()) }}">{{ $paymentRequest->status->label() }}</span></p>
            @if($paymentRequest->isOpen() && $paymentRequest->upi_id)
                <img class="qr-box" src="{{ route('public.pay.qr', $paymentRequest->token) }}" alt="UPI QR" style="margin:18px auto">
                <p><a class="btn btn-primary" href="{{ $paymentRequest->upiLink() }}">Pay with UPI</a></p>
                <p class="muted">After paying, the desk will confirm the payment. Do not close based on this screen alone.</p>
            @elseif($paymentRequest->status->value === 'paid')
                <p>This request is already paid.</p>
            @else
                <p>This request is not payable.</p>
            @endif
        </div>
    </div>
</x-guest-layout>
