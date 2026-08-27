<x-app-layout heading="Audit logs">
    <div class="card"><div class="table-wrap"><table>
        <thead><tr><th>When</th><th>User</th><th>Business</th><th>Action</th></tr></thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at->toDayDateTimeString() }}</td>
                <td>{{ $log->user?->name ?? 'System' }}</td>
                <td>{{ $log->business?->name ?? '—' }}</td>
                <td>{{ $log->action }}</td>
            </tr>
        @empty
            <tr><td colspan="4">No audit events.</td></tr>
        @endforelse
        </tbody>
    </table></div><div class="pager">{{ $logs->links() }}</div></div>
</x-app-layout>
