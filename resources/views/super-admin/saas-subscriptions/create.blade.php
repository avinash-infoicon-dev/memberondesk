<x-app-layout heading="Record SaaS subscription">
    <div class="card"><div class="card-b">
        <form method="POST" action="{{ route('super-admin.saas-subscriptions.store') }}" class="form">
            @csrf
            <label>Business
                <select name="business_id" required>
                    @foreach($businesses as $business)
                        <option value="{{ $business->id }}">{{ $business->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Plan
                <select name="saas_plan_id" required>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} · {{ format_inr($plan->price) }}</option>
                    @endforeach
                </select>
            </label>
            <label>Method
                <select name="method">
                    @foreach(\App\Enums\PaymentMethod::cases() as $method)
                        <option value="{{ $method->value }}">{{ $method->label() }}</option>
                    @endforeach
                </select>
            </label>
            <label>Reference <input name="reference"></label>
            <button class="btn btn-primary" type="submit">Activate and record payment</button>
        </form>
    </div></div>
</x-app-layout>
