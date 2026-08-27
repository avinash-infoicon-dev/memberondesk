<x-app-layout heading="Payments">
    <div class="page-head">
        <h1>Member payments</h1>
        <a class="btn btn-primary" href="{{ route('business.payments.create') }}">Record payment</a>
    </div>
    <div class="card"><div class="table-wrap"><table>
        <thead><tr><th>When</th><th>Member</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($payments as $payment)
            <tr>
                <td>{{ $payment->paid_at?->toDayDateTimeString() }}</td>
                <td>{{ $payment->member?->name }}</td>
                <td>{{ format_inr($payment->amount) }}</td>
                <td>{{ $payment->method->label() }}</td>
                <td><span class="badge {{ status_badge_class($payment->status->color()) }}">{{ $payment->status->label() }}</span></td>
            </tr>
        @empty
            <tr><td colspan="5">No payments recorded.</td></tr>
        @endforelse
        </tbody>
    </table></div><div class="pager">{{ $payments->links() }}</div></div>
</x-app-layout>
