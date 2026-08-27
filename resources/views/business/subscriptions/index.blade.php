<x-app-layout heading="Subscriptions">
    <div class="page-head">
        <h1>Subscriptions</h1>
        <a class="btn btn-primary" href="{{ route('business.subscriptions.create') }}">Assign plan</a>
    </div>
    <div class="card"><div class="table-wrap"><table>
        <thead><tr><th>Member</th><th>Plan</th><th>Period</th><th>Paid</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($subscriptions as $subscription)
            <tr>
                <td>{{ $subscription->member?->name }}</td>
                <td>{{ $subscription->plan?->name }}</td>
                <td>{{ $subscription->starts_at?->toDateString() }} → {{ $subscription->ends_at?->toDateString() }}</td>
                <td>{{ format_inr($subscription->paid_amount) }} / {{ format_inr($subscription->amount) }}</td>
                <td><span class="badge {{ status_badge_class($subscription->status->color()) }}">{{ $subscription->status->label() }}</span></td>
                <td><a class="btn btn-ghost btn-sm" href="{{ route('business.subscriptions.show', $subscription) }}">View</a></td>
            </tr>
        @empty
            <tr><td colspan="6">No subscriptions yet.</td></tr>
        @endforelse
        </tbody>
    </table></div><div class="pager">{{ $subscriptions->links() }}</div></div>
</x-app-layout>
