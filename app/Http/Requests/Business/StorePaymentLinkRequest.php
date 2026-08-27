<?php

namespace App\Http\Requests\Business;

use App\Enums\PaymentMethod;
use App\Models\PaymentRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', PaymentRequest::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', Rule::exists('members', 'id')->where('business_id', $this->user()?->business_id)],
            'subscription_id' => ['nullable', 'integer', Rule::exists('subscriptions', 'id')->where('business_id', $this->user()?->business_id)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::in([PaymentMethod::Upi->value, PaymentMethod::Online->value, PaymentMethod::Gateway->value])],
        ];
    }
}
