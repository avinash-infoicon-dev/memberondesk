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

    protected function prepareForValidation(): void
    {
        // Retrofit often sends camelCase for the file part.
        if ($this->hasFile('profileImage') && ! $this->hasFile('profile_image')) {
            $this->files->set('profile_image', $this->file('profileImage'));
        }

        if ($this->has('phone') && $this->input('phone') === '') {
            $this->merge(['phone' => null]);
        }
    }

    public function rules(): array
    {
        $businessId = $this->user()?->business_id;
        $member = $this->route('member');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            // Optional: device-local mode keeps phone only on Android.
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('members', 'phone')->where('business_id', $businessId)->ignore($member)],
            'phone_ciphertext' => ['nullable', 'string'],
            'phone_wrapped_key' => ['nullable', 'string', 'required_with:phone_ciphertext'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'status' => ['sometimes', Rule::enum(MemberStatus::class)],
            'joined_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'profile_image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
