<x-app-layout heading="Attendance">
    <div class="page-head">
        <h1>Attendance</h1>
        <a class="btn btn-primary" href="{{ route('business.attendance.scan') }}">Open scanner</a>
    </div>
    <form method="GET" class="card" style="margin-bottom:16px"><div class="card-b" style="display:flex;gap:8px">
        <input type="date" name="date" value="{{ request('date', now()->toDateString()) }}">
        <button class="btn btn-primary" type="submit">Filter</button>
    </div></form>
    <div class="card"><div class="table-wrap"><table>
        <thead><tr><th>Member</th><th>In</th><th>Out</th><th>Source</th></tr></thead>
        <tbody>
        @forelse($records as $row)
            <tr>
                <td>{{ $row->member?->name }}</td>
                <td>{{ $row->check_in_at?->format('d M Y h:i A') }}</td>
                <td>{{ $row->check_out_at?->format('h:i A') ?? '—' }}</td>
                <td>{{ $row->source->label() }}</td>
            </tr>
        @empty
            <tr><td colspan="4">No attendance for this date.</td></tr>
        @endforelse
        </tbody>
    </table></div><div class="pager">{{ $records->links() }}</div></div>
</x-app-layout>
