<?php

namespace App\Http\Requests\Business;

use App\Enums\MemberStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('member')) ?? false;
    }

    public function rules(): array
    {
        $businessId = $this->user()?->business_id;
        $member = $this->route('member');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('members', 'phone')->where('business_id', $businessId)->ignore($member)],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'status' => ['sometimes', Rule::enum(MemberStatus::class)],
            'joined_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
