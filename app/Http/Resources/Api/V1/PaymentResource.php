<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'method' => $this->method,
            'status' => $this->status,
            'reference' => $this->reference,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'member' => new MemberResource($this->whenLoaded('member')),
        ];
    }
}
