<x-app-layout heading="SaaS payments">
    <div class="card"><div class="table-wrap"><table>
        <thead><tr><th>When</th><th>Business</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($payments as $payment)
            <tr>
                <td>{{ $payment->paid_at?->toDayDateTimeString() ?? $payment->created_at->toDayDateTimeString() }}</td>
                <td>{{ $payment->business?->name }}</td>
                <td>{{ format_inr($payment->amount) }}</td>
                <td>{{ $payment->method->label() }}</td>
                <td><span class="badge {{ status_badge_class($payment->status->color()) }}">{{ $payment->status->label() }}</span></td>
            </tr>
        @empty
            <tr><td colspan="5">No SaaS payments.</td></tr>
        @endforelse
        </tbody>
    </table></div><div class="pager">{{ $payments->links() }}</div></div>
</x-app-layout>
