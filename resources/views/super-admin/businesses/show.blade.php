<x-app-layout heading="Business">
    <div class="page-head">
        <div>
            <h1>{{ $business->name }}</h1>
            <p class="muted">{{ $business->type->label() }} · {{ $business->city }}</p>
        </div>
        <a class="btn btn-ghost" href="{{ route('super-admin.businesses.edit', $business) }}">Edit</a>
    </div>
    <div class="grid grid-2">
        <div class="card"><div class="card-b">
            <p><strong>Status:</strong> <span class="badge {{ status_badge_class($business->status->color()) }}">{{ $business->status->label() }}</span></p>
            <p><strong>Owner:</strong> {{ $business->owner?->name }} ({{ $business->owner?->email }})</p>
            <p><strong>Phone:</strong> {{ $business->phone ?: '—' }}</p>
            <p><strong>UPI:</strong> {{ $business->upi_id ?: '—' }}</p>
            <p><strong>Address:</strong> {{ $business->address ?: '—' }}</p>
        </div></div>
        <div class="card"><div class="card-b">
            <p><strong>SaaS:</strong> {{ $business->currentSaasSubscription?->status->label() ?? 'None' }}</p>
            <p><strong>Plan:</strong> {{ $business->currentSaasSubscription?->plan?->name ?? '—' }}</p>
            <p><strong>Ends:</strong> {{ $business->currentSaasSubscription?->ends_at?->toDayDateTimeString() ?? '—' }}</p>
        </div></div>
    </div>
</x-app-layout>
