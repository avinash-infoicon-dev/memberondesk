<x-app-layout heading="Payment requests">
    <div class="page-head">
        <h1>UPI / online requests</h1>
        <a class="btn btn-primary" href="{{ route('business.payment-requests.create') }}">New request</a>
    </div>
    <div class="card"><div class="table-wrap"><table>
        <thead><tr><th>Member</th><th>Amount</th><th>Status</th><th>Expires</th><th></th></tr></thead>
        <tbody>
        @forelse($requests as $requestItem)
            <tr>
                <td>{{ $requestItem->member?->name }}</td>
                <td>{{ format_inr($requestItem->amount) }}</td>
                <td><span class="badge {{ status_badge_class($requestItem->status->color()) }}">{{ $requestItem->status->label() }}</span></td>
                <td>{{ $requestItem->expires_at?->toDayDateTimeString() }}</td>
                <td><a class="btn btn-ghost btn-sm" href="{{ route('business.payment-requests.show', $requestItem) }}">Open</a></td>
            </tr>
        @empty
            <tr><td colspan="5">No payment requests.</td></tr>
        @endforelse
        </tbody>
    </table></div><div class="pager">{{ $requests->links() }}</div></div>
</x-app-layout>
