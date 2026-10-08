<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOwnerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasBusinessAccess() ?? false;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed'],
            'business_name' => ['sometimes', 'required', 'string', 'max:255'],
            'business_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'business_phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'upi_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'state' => ['sometimes', 'nullable', 'string', 'max:100'],
            'pincode' => ['sometimes', 'nullable', 'string', 'max:12'],
            'whatsapp_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
