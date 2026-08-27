<x-app-layout heading="Payment request">
    <div class="page-head">
        <div>
            <h1>{{ format_inr($paymentRequest->amount) }}</h1>
            <p class="muted">{{ $paymentRequest->member?->name }} · {{ $paymentRequest->status->label() }}</p>
        </div>
        @if($paymentRequest->isOpen())
            <form method="POST" action="{{ route('business.payment-requests.confirm', $paymentRequest) }}">
                @csrf
                <button class="btn btn-primary" type="submit">Confirm received</button>
            </form>
        @endif
    </div>
    <div class="grid grid-2">
        <div class="card"><div class="card-b stack">
            <p>Share this page with the member. Do not trust a frontend “success” screen — confirm here or wait for the gateway webhook.</p>
            <p><a href="{{ route('public.pay.show', $paymentRequest->token) }}" target="_blank">{{ route('public.pay.show', $paymentRequest->token) }}</a></p>
            <p class="muted">Token {{ $paymentRequest->token }}</p>
        </div></div>
        <div class="card"><div class="card-b" style="text-align:center">
            <img class="qr-box" src="{{ route('public.pay.qr', $paymentRequest->token) }}" alt="UPI QR">
        </div></div>
    </div>
</x-app-layout>
