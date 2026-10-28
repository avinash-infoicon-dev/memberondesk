<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWhatsappWelcomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('member')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('welcome_message') && ! $this->has('message')) {
            $this->merge(['message' => $this->input('welcome_message')]);
        }

        if (! $this->exists('sent')) {
            $this->merge(['sent' => true]);
        }
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:2000'],
            'sent' => ['sometimes', 'boolean'],
        ];
    }
}
