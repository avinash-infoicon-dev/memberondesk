<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-mark">M</div>
        <div>
            <strong>Member On Desk</strong>
            <small>{{ auth()->user()->isSuperAdmin() ? 'Platform' : (auth()->user()->business?->name ?? 'Business') }}</small>
        </div>
    </div>
    <nav class="nav">
        @if(auth()->user()->isSuperAdmin())
            <div class="nav-group">Platform</div>
            <a href="{{ route('super-admin.dashboard') }}" class="{{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('super-admin.businesses.index') }}" class="{{ request()->routeIs('super-admin.businesses.*') ? 'active' : '' }}">Businesses</a>
            <a href="{{ route('super-admin.users.index') }}" class="{{ request()->routeIs('super-admin.users.*') ? 'active' : '' }}">Users</a>
            <div class="nav-group">SaaS</div>
            <a href="{{ route('super-admin.saas-plans.index') }}" class="{{ request()->routeIs('super-admin.saas-plans.*') ? 'active' : '' }}">Plans</a>
            <a href="{{ route('super-admin.saas-subscriptions.index') }}" class="{{ request()->routeIs('super-admin.saas-subscriptions.*') ? 'active' : '' }}">Subscriptions</a>
            <a href="{{ route('super-admin.saas-payments.index') }}" class="{{ request()->routeIs('super-admin.saas-payments.*') ? 'active' : '' }}">Payments</a>
            <div class="nav-group">Insights</div>
            <a href="{{ route('super-admin.reports.index') }}" class="{{ request()->routeIs('super-admin.reports.*') ? 'active' : '' }}">Reports</a>
            <a href="{{ route('super-admin.audit-logs.index') }}" class="{{ request()->routeIs('super-admin.audit-logs.*') ? 'active' : '' }}">Audit logs</a>
        @else
            <div class="nav-group">Desk</div>
            <a href="{{ route('business.dashboard') }}" class="{{ request()->routeIs('business.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('business.members.index') }}" class="{{ request()->routeIs('business.members.*') ? 'active' : '' }}">Members</a>
            <a href="{{ route('business.plans.index') }}" class="{{ request()->routeIs('business.plans.*') ? 'active' : '' }}">Plans</a>
            <a href="{{ route('business.subscriptions.index') }}" class="{{ request()->routeIs('business.subscriptions.*') ? 'active' : '' }}">Subscriptions</a>
            <a href="{{ route('business.attendance.scan') }}" class="{{ request()->routeIs('business.attendance.scan') ? 'active' : '' }}">Scan QR</a>
            <a href="{{ route('business.attendance.index') }}" class="{{ request()->routeIs('business.attendance.index') ? 'active' : '' }}">Attendance</a>
            <div class="nav-group">Money</div>
            <a href="{{ route('business.payments.index') }}" class="{{ request()->routeIs('business.payments.*') ? 'active' : '' }}">Payments</a>
            <a href="{{ route('business.payment-requests.index') }}" class="{{ request()->routeIs('business.payment-requests.*') ? 'active' : '' }}">Payment requests</a>
            <a href="{{ route('business.reminders.index') }}" class="{{ request()->routeIs('business.reminders.*') ? 'active' : '' }}">WhatsApp</a>
            <div class="nav-group">Account</div>
            <a href="{{ route('business.reports.index') }}" class="{{ request()->routeIs('business.reports.*') ? 'active' : '' }}">Reports</a>
            <a href="{{ route('business.profile.edit') }}" class="{{ request()->routeIs('business.profile.*') ? 'active' : '' }}">Business</a>
            <a href="{{ route('business.saas.index') }}" class="{{ request()->routeIs('business.saas.*') ? 'active' : '' }}">SaaS billing</a>
        @endif
    </nav>
</aside>
