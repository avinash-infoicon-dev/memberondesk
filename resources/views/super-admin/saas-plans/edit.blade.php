<x-app-layout heading="Edit SaaS plan">
    <div class="card"><div class="card-b">
        <form method="POST" action="{{ route('super-admin.saas-plans.update', $plan) }}" class="form">
            @csrf @method('PUT')
            <div class="form-row">
                <label>Name <input name="name" value="{{ old('name', $plan->name) }}" required></label>
                <label>Slug <input name="slug" value="{{ old('slug', $plan->slug) }}" required></label>
            </div>
            <div class="form-row">
                <label>Interval
                    <select name="interval">
                        @foreach(\App\Enums\SaasPlanInterval::cases() as $interval)
                            <option value="{{ $interval->value }}" @selected(old('interval', $plan->interval->value) === $interval->value)>{{ $interval->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Price (INR) <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price) }}" required></label>
            </div>
            <label>Max members <input type="number" name="max_members" value="{{ old('max_members', $plan->max_members) }}"></label>
            <label style="display:flex;gap:8px;align-items:center;font-weight:500"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active)) style="width:auto"> Active</label>
            <button class="btn btn-primary" type="submit">Save</button>
        </form>
    </div></div>
</x-app-layout>
