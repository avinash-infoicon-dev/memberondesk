<x-app-layout heading="WhatsApp reminders">
    <div class="page-head">
        <h1>WhatsApp</h1>
        <form method="POST" action="{{ route('business.reminders.expiring') }}">
            @csrf
            <button class="btn btn-primary" type="submit">Queue expiry reminders</button>
        </form>
    </div>
    <div class="grid grid-2">
        <div class="card"><div class="card-b">
            <form method="POST" action="{{ route('business.reminders.store') }}" class="form">
                @csrf
                <label>Member
                    <select name="member_id" required>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }} · {{ $member->phone }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Type
                    <select name="type">
                        @foreach(\App\Enums\NotificationType::cases() as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Custom message <textarea name="message" rows="3" placeholder="Leave blank to use the template"></textarea></label>
                <button class="btn btn-primary" type="submit">Queue message</button>
            </form>
        </div></div>
        <div class="card">
            <div class="card-h">Recent messages</div>
            <div class="table-wrap"><table>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->member?->name }}</td>
                        <td>{{ $log->type->label() }}</td>
                        <td>{{ $log->status->value }}</td>
                    </tr>
                @empty
                    <tr><td>No messages yet.</td></tr>
                @endforelse
            </table></div>
            <div class="pager">{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>
