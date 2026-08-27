<x-app-layout heading="New business">
    <div class="page-head"><h1>Register a business</h1></div>
    <div class="card"><div class="card-b">
        <form method="POST" action="{{ route('super-admin.businesses.store') }}" class="form">
            @csrf
            <div class="form-row">
                <label>Business name <input name="name" value="{{ old('name') }}" required></label>
                <label>Type
                    <select name="type">
                        @foreach(\App\Enums\BusinessType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="form-row">
                <label>Business email <input type="email" name="email" value="{{ old('email') }}"></label>
                <label>Phone <input name="phone" value="{{ old('phone') }}"></label>
            </div>
            <div class="form-row">
                <label>UPI ID <input name="upi_id" value="{{ old('upi_id') }}"></label>
                <label>Status
                    <select name="status">
                        @foreach(\App\Enums\BusinessStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status', 'active') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label>Address <input name="address" value="{{ old('address') }}"></label>
            <div class="form-row">
                <label>City <input name="city" value="{{ old('city') }}"></label>
                <label>State <input name="state" value="{{ old('state') }}"></label>
            </div>
            <label>Pincode <input name="pincode" value="{{ old('pincode') }}"></label>
            <h3>Owner account</h3>
            <div class="form-row">
                <label>Owner name <input name="owner_name" value="{{ old('owner_name') }}" required></label>
                <label>Owner email <input type="email" name="owner_email" value="{{ old('owner_email') }}" required></label>
            </div>
            <div class="form-row">
                <label>Owner phone <input name="owner_phone" value="{{ old('owner_phone') }}"></label>
                <label>Owner password <input type="password" name="owner_password" required></label>
            </div>
            <button class="btn btn-primary" type="submit">Create business</button>
        </form>
    </div></div>
</x-app-layout>
