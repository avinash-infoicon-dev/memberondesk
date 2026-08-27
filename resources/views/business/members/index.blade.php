<x-app-layout heading="Members">
    <div class="page-head">
        <div>
            <h1>Members</h1>
            <p class="muted">Register, assign plans and print QR cards.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('business.members.create') }}">Register member</a>
    </div>
    <form class="card" method="GET" style="margin-bottom:16px">
        <div class="card-b" style="display:flex;gap:8px">
            <input class="search" type="search" name="q" value="{{ request('q') }}" placeholder="Search name, phone or code">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>
    <div class="card"><div class="table-wrap"><table>
        <thead><tr><th>Code</th><th>Name</th><th>Phone</th><th>Plan</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($members as $member)
            <tr>
                <td>{{ $member->member_code }}</td>
                <td><a href="{{ route('business.members.show', $member) }}"><strong>{{ $member->name }}</strong></a></td>
                <td>{{ $member->phone }}</td>
                <td>{{ $member->activeSubscription?->plan?->name ?? '—' }}</td>
                <td>{{ $member->status->label() }}</td>
                <td><a class="btn btn-ghost btn-sm" href="{{ route('business.members.edit', $member) }}">Edit</a></td>
            </tr>
        @empty
            <tr><td colspan="6">No members yet.</td></tr>
        @endforelse
        </tbody>
    </table></div><div class="pager">{{ $members->links() }}</div></div>
</x-app-layout>
