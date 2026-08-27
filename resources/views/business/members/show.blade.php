<x-app-layout heading="Member">
    <div class="page-head">
        <div>
            <h1>{{ $member->name }}</h1>
            <p class="muted">{{ $member->member_code }} · {{ $member->phone }}</p>
        </div>
        <div style="display:flex;gap:8px">
            <a class="btn btn-ghost" href="{{ route('business.subscriptions.create', ['member_id' => $member->id]) }}">Assign plan</a>
            <a class="btn btn-primary" href="{{ route('business.members.edit', $member) }}">Edit</a>
        </div>
    </div>
    <div class="grid grid-2">
        <div class="card"><div class="card-b stack">
            <p><strong>Email:</strong> {{ $member->email ?: '—' }}</p>
            <p><strong>Status:</strong> {{ $member->status->label() }}</p>
            <p><strong>Joined:</strong> {{ $member->joined_at?->toDateString() }}</p>
            <form method="POST" action="{{ route('business.members.qr.regenerate', $member) }}">
                @csrf
                <button class="btn btn-ghost" type="submit">Regenerate QR</button>
            </form>
        </div></div>
        <div class="card"><div class="card-b" style="text-align:center">
            @if($member->activeQrCode)
                <img class="qr-box" src="{{ route('business.members.qr.image', $member) }}" alt="Member QR">
                <p class="muted">{{ $member->activeQrCode->token }}</p>
            @endif
        </div></div>
    </div>
    <div class="card" style="margin-top:16px">
        <div class="card-h">Subscriptions</div>
        <div class="table-wrap"><table>
            @foreach($member->subscriptions as $subscription)
                <tr>
                    <td>{{ $subscription->plan?->name }}</td>
                    <td>{{ $subscription->status->label() }}</td>
                    <td>{{ $subscription->starts_at?->toDateString() }} → {{ $subscription->ends_at?->toDateString() }}</td>
                    <td>{{ format_inr($subscription->paid_amount) }} / {{ format_inr($subscription->amount) }}</td>
                </tr>
            @endforeach
        </table></div>
    </div>
</x-app-layout>
