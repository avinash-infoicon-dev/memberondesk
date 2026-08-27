<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'token' => $this->token,
            'method' => $this->method,
            'upi_id' => $this->upi_id,
            'upi_link' => $this->upiLink(),
            'pay_url' => route('public.pay.show', $this->token),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'member' => new MemberResource($this->whenLoaded('member')),
        ];
    }
}
