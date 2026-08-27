<?php

namespace App\Http\Requests\SuperAdmin;

use App\Enums\SaasPlanInterval;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSaasPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        $plan = $this->route('saas_plan');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('saas_plans', 'slug')->ignore($plan)],
            'interval' => ['required', Rule::enum(SaasPlanInterval::class)],
            'price' => ['required', 'numeric', 'min:0'],
            'max_members' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
