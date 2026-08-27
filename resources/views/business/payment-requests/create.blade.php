<x-app-layout heading="Create payment request">
    <div class="card"><div class="card-b">
        <form method="POST" action="{{ route('business.payment-requests.store') }}" class="form">
            @csrf
            <label>Member
                <select name="member_id" required>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Subscription
                <select name="subscription_id">
                    <option value="">Optional</option>
                    @foreach($subscriptions as $subscription)
                        <option value="{{ $subscription->id }}">{{ $subscription->member?->name }} · due {{ format_inr($subscription->balance()) }}</option>
                    @endforeach
                </select>
            </label>
            <div class="form-row">
                <label>Amount <input type="number" step="0.01" name="amount" required></label>
                <label>Method
                    <select name="method">
                        <option value="upi">UPI</option>
                        <option value="online">Online</option>
                        <option value="gateway">Gateway</option>
                    </select>
                </label>
            </div>
            <button class="btn btn-primary" type="submit">Generate request</button>
        </form>
    </div></div>
</x-app-layout>
