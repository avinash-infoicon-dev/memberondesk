<x-app-layout heading="Desk overview">
    @if($business && $business->status->value !== 'active')
        <div class="alert alert-error">This business is {{ $business->status->label() }}. Contact the platform administrator.</div>
    @endif
    <div class="page-head">
        <div>
            <h1>{{ $business?->name ?? 'Your desk' }}</h1>
            <p class="muted">Today's floor, renewals and collections.</p>
        </div>
        <div style="display:flex;gap:8px">
            <a class="btn btn-ghost" href="{{ route('business.attendance.scan') }}">Scan QR</a>
            <a class="btn btn-primary" href="{{ route('business.members.create') }}">Register member</a>
        </div>
    </div>
    <div class="grid grid-3">
        <div class="stat"><span>Members</span><strong>{{ $stats['members'] }}</strong></div>
        <div class="stat"><span>Today's attendance</span><strong>{{ $stats['todays_attendance'] }}</strong></div>
        <div class="stat"><span>Expiring (7 days)</span><strong>{{ $stats['expiring_members'] }}</strong></div>
        <div class="stat"><span>Expired</span><strong>{{ $stats['expired_members'] }}</strong></div>
        <div class="stat"><span>Pending payments</span><strong>{{ $stats['pending_payments'] }}</strong></div>
        <div class="stat"><span>Revenue / this month</span><strong>{{ format_inr($stats['revenue']) }}</strong><div class="muted">{{ format_inr($stats['month_revenue']) }} this month</div></div>
    </div>
    <div class="grid grid-2" style="margin-top:16px">
        <div class="card">
            <div class="card-h">Today's check-ins</div>
            <div class="table-wrap"><table>
                @forelse($todayAttendance as $row)
                    <tr><td>{{ $row->member?->name }}</td><td>{{ $row->check_in_at?->format('h:i A') }}</td></tr>
                @empty
                    <tr><td>No check-ins yet.</td></tr>
                @endforelse
            </table></div>
        </div>
        <div class="card">
            <div class="card-h">Expiring soon</div>
            <div class="table-wrap"><table>
                @forelse($expiring as $row)
                    <tr><td>{{ $row->member?->name }}</td><td>{{ $row->ends_at?->toDateString() }}</td></tr>
                @empty
                    <tr><td>No upcoming expiries.</td></tr>
                @endforelse
            </table></div>
        </div>
    </div>
</x-app-layout>
