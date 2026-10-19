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
            'profile_image_url' => $this->profile_image_url,
            'status' => $status->value,
            'status_label' => $status->label(),
            'account_status' => $this->status,
            'joined_at' => $this->joined_at?->toDateString(),
            'qr_token' => $this->whenLoaded('activeQrCode', fn () => $this->activeQrCode?->token),
            'current_plan_name' => $this->when(
                $this->relationLoaded('activeSubscription') || $this->relationLoaded('lastActiveSubscription'),
                fn () => $this->activeSubscription?->plan?->name
                    ?? $this->lastActiveSubscription?->plan?->name
            ),
            'last_active_plan_name' => $this->when(
                $this->relationLoaded('lastActiveSubscription'),
                fn () => $this->lastActiveSubscription?->plan?->name
            ),
            'active_subscription' => $this->whenLoaded('activeSubscription', fn () => $this->activeSubscription ? [
                'id' => $this->activeSubscription->id,
                'status' => $this->activeSubscription->status,
                'starts_at' => $this->activeSubscription->starts_at?->toDateString(),
                'ends_at' => $this->activeSubscription->ends_at?->toDateString(),
                'plan_id' => $this->activeSubscription->membership_plan_id,
                'plan_name' => $this->activeSubscription->plan?->name,
            ] : null),
            'last_active_subscription' => $this->whenLoaded('lastActiveSubscription', fn () => $this->lastActiveSubscription ? [
                'id' => $this->lastActiveSubscription->id,
                'status' => $this->lastActiveSubscription->status,
                'starts_at' => $this->lastActiveSubscription->starts_at?->toDateString(),
                'ends_at' => $this->lastActiveSubscription->ends_at?->toDateString(),
                'plan_id' => $this->lastActiveSubscription->membership_plan_id,
                'plan_name' => $this->lastActiveSubscription->plan?->name,
            ] : null),
        ];
    }
}
