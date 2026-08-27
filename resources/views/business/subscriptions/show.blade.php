<x-app-layout heading="Subscription">
    <div class="page-head">
        <div>
            <h1>{{ $subscription->member?->name }}</h1>
            <p class="muted">{{ $subscription->plan?->name }} · {{ $subscription->status->label() }}</p>
        </div>
        @if($subscription->status->value !== 'cancelled')
            <form method="POST" action="{{ route('business.subscriptions.cancel', $subscription) }}">
                @csrf
                <button class="btn btn-danger" type="submit">Cancel</button>
            </form>
        @endif
    </div>
    <div class="grid grid-2">
        <div class="card"><div class="card-b">
            <p>{{ $subscription->starts_at?->toDateString() }} → {{ $subscription->ends_at?->toDateString() }}</p>
            <p>Amount {{ format_inr($subscription->amount) }} · Paid {{ format_inr($subscription->paid_amount) }} · Due {{ format_inr($subscription->balance()) }}</p>
        </div></div>
        <div class="card"><div class="card-h">Payments</div>
            <div class="table-wrap"><table>
                @forelse($subscription->payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at?->toDateString() }}</td>
                        <td>{{ format_inr($payment->amount) }}</td>
                        <td>{{ $payment->method->label() }}</td>
                    </tr>
                @empty
                    <tr><td>No payments yet.</td></tr>
                @endforelse
            </table></div>
        </div>
    </div>
</x-app-layout>
