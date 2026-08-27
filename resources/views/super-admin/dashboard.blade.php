<x-app-layout heading="Platform overview">
    <div class="page-head">
        <div>
            <h1>Super admin</h1>
            <p class="muted">Businesses, SaaS billing and platform activity.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('super-admin.businesses.create') }}">Add business</a>
    </div>
    <div class="grid grid-3">
        <div class="stat"><span>Businesses</span><strong>{{ $stats['businesses'] }}</strong></div>
        <div class="stat"><span>Members</span><strong>{{ $stats['members'] }}</strong></div>
        <div class="stat"><span>Active subscriptions</span><strong>{{ $stats['active_subscriptions'] }}</strong></div>
        <div class="stat"><span>Expired subscriptions</span><strong>{{ $stats['expired_subscriptions'] }}</strong></div>
        <div class="stat"><span>Member revenue</span><strong>{{ format_inr($stats['member_revenue']) }}</strong></div>
        <div class="stat"><span>SaaS revenue</span><strong>{{ format_inr($stats['saas_revenue']) }}</strong></div>
    </div>
</x-app-layout>
