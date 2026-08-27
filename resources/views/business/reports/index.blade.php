<x-app-layout heading="Reports">
    <div class="grid grid-2">
        <div class="card">
            <div class="card-h">Expiring</div>
            <div class="table-wrap"><table>
                @forelse($expiring as $row)
                    <tr><td>{{ $row->member?->name }}</td><td>{{ $row->ends_at?->toDateString() }}</td></tr>
                @empty
                    <tr><td>None</td></tr>
                @endforelse
            </table></div>
        </div>
        <div class="card">
            <div class="card-h">Expired</div>
            <div class="table-wrap"><table>
                @forelse($expired as $row)
                    <tr><td>{{ $row->member?->name }}</td><td>{{ $row->ends_at?->toDateString() }}</td></tr>
                @empty
                    <tr><td>None</td></tr>
                @endforelse
            </table></div>
        </div>
        <div class="card">
            <div class="card-h">Pending dues</div>
            <div class="table-wrap"><table>
                @forelse($pending as $row)
                    <tr><td>{{ $row->member?->name }}</td><td>{{ format_inr($row->balance()) }}</td></tr>
                @empty
                    <tr><td>None</td></tr>
                @endforelse
            </table></div>
        </div>
        <div class="card">
            <div class="card-h">This month's collections</div>
            <div class="table-wrap"><table>
                @forelse($payments as $row)
                    <tr><td>{{ $row->member?->name }}</td><td>{{ format_inr($row->amount) }}</td></tr>
                @empty
                    <tr><td>None</td></tr>
                @endforelse
            </table></div>
        </div>
    </div>
</x-app-layout>
