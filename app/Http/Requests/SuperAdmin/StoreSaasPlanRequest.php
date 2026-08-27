<?php

namespace App\Http\Requests\SuperAdmin;

use App\Enums\SaasPlanInterval;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaasPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:saas_plans,slug'],
            'interval' => ['required', Rule::enum(SaasPlanInterval::class)],
            'price' => ['required', 'numeric', 'min:0'],
            'max_members' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
