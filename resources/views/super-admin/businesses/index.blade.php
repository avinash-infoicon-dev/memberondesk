<x-app-layout heading="Businesses">
    <div class="page-head">
        <div>
            <h1>Businesses</h1>
            <p class="muted">Gym and library tenants on the platform.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('super-admin.businesses.create') }}">New business</a>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Business</th>
                    <th>Owner</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>SaaS</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($businesses as $business)
                    <tr>
                        <td><a href="{{ route('super-admin.businesses.show', $business) }}"><strong>{{ $business->name }}</strong></a></td>
                        <td>{{ $business->owner?->name }}<div class="muted">{{ $business->owner?->email }}</div></td>
                        <td>{{ $business->type->label() }}</td>
                        <td><span class="badge {{ status_badge_class($business->status->color()) }}">{{ $business->status->label() }}</span></td>
                        <td>{{ $business->currentSaasSubscription?->status->label() ?? '—' }}</td>
                        <td><a class="btn btn-ghost btn-sm" href="{{ route('super-admin.businesses.edit', $business) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6">No businesses yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pager">{{ $businesses->links() }}</div>
    </div>
</x-app-layout>
