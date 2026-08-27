<x-app-layout heading="SaaS subscriptions">
    <div class="page-head">
        <h1>SaaS subscriptions</h1>
        <a class="btn btn-primary" href="{{ route('super-admin.saas-subscriptions.create') }}">Record subscription</a>
    </div>
    <div class="card"><div class="table-wrap"><table>
        <thead><tr><th>Business</th><th>Plan</th><th>Status</th><th>Period</th></tr></thead>
        <tbody>
        @forelse($subscriptions as $subscription)
            <tr>
                <td>{{ $subscription->business?->name }}</td>
                <td>{{ $subscription->plan?->name }}</td>
                <td><span class="badge {{ status_badge_class($subscription->status->color()) }}">{{ $subscription->status->label() }}</span></td>
                <td>{{ $subscription->starts_at?->toDateString() }} → {{ $subscription->ends_at?->toDateString() }}</td>
            </tr>
        @empty
            <tr><td colspan="4">No SaaS subscriptions.</td></tr>
        @endforelse
        </tbody>
    </table></div><div class="pager">{{ $subscriptions->links() }}</div></div>
</x-app-layout>
