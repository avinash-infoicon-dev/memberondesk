<x-app-layout heading="SaaS billing">
    <div class="page-head">
        <div>
            <h1>Platform subscription</h1>
            <p class="muted">This is what you pay Member On Desk — separate from gym/library member collections.</p>
        </div>
    </div>
    <div class="grid grid-2">
        <div class="card"><div class="card-b">
            <p><strong>Current status:</strong> {{ $subscription?->status->label() ?? 'None' }}</p>
            <p><strong>Plan:</strong> {{ $subscription?->plan?->name ?? '—' }}</p>
            <p><strong>Valid until:</strong> {{ $subscription?->ends_at?->toDayDateTimeString() ?? '—' }}</p>
        </div></div>
        <div class="card"><div class="card-h">Available plans</div>
            <div class="card-b">
                @foreach($plans as $plan)
                    <p><strong>{{ $plan->name }}</strong> · {{ format_inr($plan->price) }} / {{ $plan->interval->label() }}</p>
                @endforeach
                <p class="muted">Ask the platform admin to record a SaaS payment after you pay ₹499/month or ₹4,999/year.</p>
            </div>
        </div>
    </div>
</x-app-layout>
