<x-app-layout heading="Edit business">
    <div class="page-head"><h1>Edit {{ $business->name }}</h1></div>
    <div class="card"><div class="card-b">
        <form method="POST" action="{{ route('super-admin.businesses.update', $business) }}" class="form">
            @csrf @method('PUT')
            <div class="form-row">
                <label>Business name <input name="name" value="{{ old('name', $business->name) }}" required></label>
                <label>Type
                    <select name="type">
                        @foreach(\App\Enums\BusinessType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('type', $business->type->value) === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="form-row">
                <label>Email <input type="email" name="email" value="{{ old('email', $business->email) }}"></label>
                <label>Phone <input name="phone" value="{{ old('phone', $business->phone) }}"></label>
            </div>
            <label>UPI ID <input name="upi_id" value="{{ old('upi_id', $business->upi_id) }}"></label>
            <label>Address <input name="address" value="{{ old('address', $business->address) }}"></label>
            <div class="form-row">
                <label>City <input name="city" value="{{ old('city', $business->city) }}"></label>
                <label>State <input name="state" value="{{ old('state', $business->state) }}"></label>
            </div>
            <label>Pincode <input name="pincode" value="{{ old('pincode', $business->pincode) }}"></label>
            <label>Status
                <select name="status">
                    @foreach(\App\Enums\BusinessStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $business->status->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </label>
            <label style="display:flex;gap:8px;align-items:center;font-weight:500">
                <input type="checkbox" name="whatsapp_enabled" value="1" @checked(old('whatsapp_enabled', $business->whatsapp_enabled)) style="width:auto"> WhatsApp enabled
            </label>
            <h3>Member mobile privacy</h3>
            <label>How should member phone numbers be stored?
                <select name="member_phone_storage" required>
                    @foreach(\App\Enums\MemberPhoneStorage::cases() as $mode)
                        <option value="{{ $mode->value }}" @selected(old('member_phone_storage', $business->member_phone_storage?->value ?? 'device_local') === $mode->value)>{{ $mode->label() }}</option>
                    @endforeach
                </select>
            </label>
            <p class="muted" style="margin-top:-8px">
                <strong>Device only:</strong> phone stays on the Android app; server keeps other member data.<br>
                <strong>Encrypted on server:</strong> app encrypts phone with K1; server stores ciphertext and K1 wrapped by recovery key K2.
            </p>
            <button class="btn btn-primary" type="submit">Save</button>
        </form>
    </div></div>
</x-app-layout>
