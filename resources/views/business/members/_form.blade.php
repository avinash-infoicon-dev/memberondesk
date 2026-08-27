<div class="form-row">
    <label>Full name <input name="name" value="{{ old('name', $member->name ?? '') }}" required></label>
    <label>Phone <input name="phone" value="{{ old('phone', $member->phone ?? '') }}" required></label>
</div>
<div class="form-row">
    <label>Email <input type="email" name="email" value="{{ old('email', $member->email ?? '') }}"></label>
    <label>Gender
        <select name="gender">
            <option value="">—</option>
            @foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $value => $label)
                <option value="{{ $value }}" @selected(old('gender', $member->gender ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
</div>
<div class="form-row">
    <label>Date of birth <input type="date" name="date_of_birth" value="{{ old('date_of_birth', isset($member) ? $member->date_of_birth?->toDateString() : '') }}"></label>
    <label>Joined on <input type="date" name="joined_at" value="{{ old('joined_at', isset($member) ? $member->joined_at?->toDateString() : now()->toDateString()) }}"></label>
</div>
<label>Address <input name="address" value="{{ old('address', $member->address ?? '') }}"></label>
<div class="form-row">
    <label>Emergency contact <input name="emergency_contact_name" value="{{ old('emergency_contact_name', $member->emergency_contact_name ?? '') }}"></label>
    <label>Emergency phone <input name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $member->emergency_contact_phone ?? '') }}"></label>
</div>
@isset($member)
    <label>Status
        <select name="status">
            @foreach(\App\Enums\MemberStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $member->status->value) === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </label>
@endisset
<label>Notes <textarea name="notes" rows="3">{{ old('notes', $member->notes ?? '') }}</textarea></label>
