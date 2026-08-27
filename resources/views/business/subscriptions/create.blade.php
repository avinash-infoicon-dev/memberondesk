<x-app-layout heading="Assign subscription">
    <div class="card"><div class="card-b">
        <form method="POST" action="{{ route('business.subscriptions.store') }}" class="form">
            @csrf
            <label>Member
                <select name="member_id" required>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}" @selected((string) $selectedMember === (string) $member->id)>{{ $member->name }} · {{ $member->phone }}</option>
                    @endforeach
                </select>
            </label>
            <label>Plan
                <select name="membership_plan_id" required>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} · {{ $plan->duration_days }} days · {{ format_inr($plan->price) }}</option>
                    @endforeach
                </select>
            </label>
            <label>Start date <input type="date" name="starts_at" value="{{ old('starts_at', now()->toDateString()) }}"></label>
            <label>Notes <textarea name="notes">{{ old('notes') }}</textarea></label>
            <label style="display:flex;gap:8px;align-items:center;font-weight:500">
                <input type="checkbox" name="collect_cash" value="1" style="width:auto"> Collect full amount in cash now
            </label>
            <button class="btn btn-primary" type="submit">Create subscription</button>
        </form>
    </div></div>
</x-app-layout>
