<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpiDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isBusinessOwner() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('accountHolderName') && ! $this->has('account_holder_name')) {
            $this->merge(['account_holder_name' => $this->input('accountHolderName')]);
        }

        foreach (['upiQr', 'qr_code', 'upi_qr_code', 'qrCode'] as $alias) {
            if ($this->hasFile($alias) && ! $this->hasFile('upi_qr')) {
                $this->files->set('upi_qr', $this->file($alias));
            }
        }
    }

    public function rules(): array
    {
        return [
            'account_holder_name' => ['required', 'string', 'max:255'],
            'upi_qr' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
