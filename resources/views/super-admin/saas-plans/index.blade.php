<x-app-layout heading="SaaS plans">
    <div class="page-head">
        <h1>SaaS plans</h1>
        <a class="btn btn-primary" href="{{ route('super-admin.saas-plans.create') }}">New plan</a>
    </div>
    <div class="card"><div class="table-wrap"><table>
        <thead><tr><th>Name</th><th>Interval</th><th>Price</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($plans as $plan)
            <tr>
                <td>{{ $plan->name }}</td>
                <td>{{ $plan->interval->label() }}</td>
                <td>{{ format_inr($plan->price) }}</td>
                <td>{{ $plan->is_active ? 'Active' : 'Hidden' }}</td>
                <td><a class="btn btn-ghost btn-sm" href="{{ route('super-admin.saas-plans.edit', $plan) }}">Edit</a></td>
            </tr>
        @empty
            <tr><td colspan="5">No plans.</td></tr>
        @endforelse
        </tbody>
    </table></div><div class="pager">{{ $plans->links() }}</div></div>
</x-app-layout>
