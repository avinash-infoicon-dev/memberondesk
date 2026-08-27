<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'starts_at' => $this->starts_at?->toDateString(),
            'ends_at' => $this->ends_at?->toDateString(),
            'amount' => (float) $this->amount,
            'paid_amount' => (float) $this->paid_amount,
            'balance' => $this->balance(),
            'member' => new MemberResource($this->whenLoaded('member')),
            'plan' => new MembershipPlanResource($this->whenLoaded('plan')),
        ];
    }
}
