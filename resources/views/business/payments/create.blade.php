<x-app-layout heading="Record payment">
    <div class="card"><div class="card-b">
        <form method="POST" action="{{ route('business.payments.store') }}" class="form">
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
                    <option value="">Latest / none</option>
                    @foreach($subscriptions as $subscription)
                        <option value="{{ $subscription->id }}">{{ $subscription->member?->name }} · {{ $subscription->plan?->name }} · due {{ format_inr($subscription->balance()) }}</option>
                    @endforeach
                </select>
            </label>
            <div class="form-row">
                <label>Amount <input type="number" step="0.01" name="amount" required></label>
                <label>Method
                    <select name="method">
                        @foreach(\App\Enums\PaymentMethod::cases() as $method)
                            <option value="{{ $method->value }}">{{ $method->label() }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label>Reference <input name="reference"></label>
            <label>Notes <textarea name="notes"></textarea></label>
            <button class="btn btn-primary" type="submit">Mark paid</button>
        </form>
    </div></div>
</x-app-layout>
