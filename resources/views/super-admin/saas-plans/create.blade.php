<x-app-layout heading="New SaaS plan">
    <div class="card"><div class="card-b">
        <form method="POST" action="{{ route('super-admin.saas-plans.store') }}" class="form">
            @csrf
            <div class="form-row">
                <label>Name <input name="name" value="{{ old('name') }}" required></label>
                <label>Slug <input name="slug" value="{{ old('slug') }}" required></label>
            </div>
            <div class="form-row">
                <label>Interval
                    <select name="interval">
                        @foreach(\App\Enums\SaasPlanInterval::cases() as $interval)
                            <option value="{{ $interval->value }}">{{ $interval->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Price (INR) <input type="number" step="0.01" name="price" value="{{ old('price') }}" required></label>
            </div>
            <label>Max members <input type="number" name="max_members" value="{{ old('max_members') }}"></label>
            <label style="display:flex;gap:8px;align-items:center;font-weight:500"><input type="checkbox" name="is_active" value="1" checked style="width:auto"> Active</label>
            <button class="btn btn-primary" type="submit">Create</button>
        </form>
    </div></div>
</x-app-layout>
