<?php

namespace App\Http\Requests\Business;

use App\Enums\PaymentMethod;
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
            'amount' => ['sometimes', 'nullable', 'numeric', 'min:0.01'],
            'method' => ['sometimes', 'nullable', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:255'],
            'payment_notes' => ['nullable', 'string'],
        ];
    }

    public function paymentMethod(): ?PaymentMethod
    {
        if ($this->filled('method')) {
            return $this->enum('method', PaymentMethod::class);
        }

        if ($this->boolean('collect_cash')) {
            return PaymentMethod::Cash;
        }

        return null;
    }

    public function plan(): MembershipPlan
    {
        return MembershipPlan::query()->findOrFail($this->integer('membership_plan_id'));
    }
}
