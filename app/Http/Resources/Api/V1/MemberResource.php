<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->displayStatus();

        return [
            'id' => $this->id,
            'member_code' => $this->member_code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'status' => $status->value,
            'status_label' => $status->label(),
            'account_status' => $this->status,
            'joined_at' => $this->joined_at?->toDateString(),
            'qr_token' => $this->whenLoaded('activeQrCode', fn () => $this->activeQrCode?->token),
            'active_subscription' => $this->whenLoaded('activeSubscription', fn () => $this->activeSubscription ? [
                'id' => $this->activeSubscription->id,
                'status' => $this->activeSubscription->status,
                'starts_at' => $this->activeSubscription->starts_at?->toDateString(),
                'ends_at' => $this->activeSubscription->ends_at?->toDateString(),
            ] : null),
        ];
    }
}
