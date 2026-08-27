<?php

namespace App\Http\Requests\Business;

use App\Models\MembershipPlan;
use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Subscription::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', Rule::exists('members', 'id')->where('business_id', $this->user()?->business_id)],
            'membership_plan_id' => ['required', 'integer', Rule::exists('membership_plans', 'id')->where('business_id', $this->user()?->business_id)],
            'starts_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'collect_cash' => ['sometimes', 'boolean'],
        ];
    }

    public function plan(): MembershipPlan
    {
        return MembershipPlan::query()->findOrFail($this->integer('membership_plan_id'));
    }
}
