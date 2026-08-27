<x-app-layout heading="Business profile">
    <div class="card"><div class="card-b">
        <form method="POST" action="{{ route('business.profile.update') }}" class="form">
            @csrf @method('PUT')
            <label>Name <input name="name" value="{{ old('name', $business->name) }}" required></label>
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
            <label style="display:flex;gap:8px;align-items:center;font-weight:500">
                <input type="checkbox" name="whatsapp_enabled" value="1" @checked(old('whatsapp_enabled', $business->whatsapp_enabled)) style="width:auto"> WhatsApp reminders enabled
            </label>
            <button class="btn btn-primary" type="submit">Save</button>
        </form>
    </div></div>
</x-app-layout>
