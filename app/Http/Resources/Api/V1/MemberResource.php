<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->displayStatus();

        // Prefer the latest assigned Active plan (by id), not merely the
        // date-window row — members can have overlapping Active subscriptions.
        $current = $this->relationLoaded('latestActiveSubscription')
            ? $this->latestActiveSubscription
            : ($this->relationLoaded('activeSubscription') ? $this->activeSubscription : null);

        $lastActive = $this->relationLoaded('lastActiveSubscription')
            ? $this->lastActiveSubscription
            : null;

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
                $this->relationLoaded('latestActiveSubscription')
                    || $this->relationLoaded('activeSubscription')
                    || $this->relationLoaded('lastActiveSubscription'),
                fn () => $current?->plan?->name ?? $lastActive?->plan?->name
            ),
            'last_active_plan_name' => $this->when(
                $this->relationLoaded('lastActiveSubscription'),
                fn () => $lastActive?->plan?->name
            ),
            'active_subscription' => $this->when(
                $this->relationLoaded('latestActiveSubscription') || $this->relationLoaded('activeSubscription'),
                fn () => $current ? [
                    'id' => $current->id,
                    'status' => $current->status,
                    'starts_at' => $current->starts_at?->toDateString(),
                    'ends_at' => $current->ends_at?->toDateString(),
                    'plan_id' => $current->membership_plan_id,
                    'plan_name' => $current->plan?->name,
                ] : null
            ),
            'last_active_subscription' => $this->whenLoaded('lastActiveSubscription', fn () => $lastActive ? [
                'id' => $lastActive->id,
                'status' => $lastActive->status,
                'starts_at' => $lastActive->starts_at?->toDateString(),
                'ends_at' => $lastActive->ends_at?->toDateString(),
                'plan_id' => $lastActive->membership_plan_id,
                'plan_name' => $lastActive->plan?->name,
            ] : null),
        ];
    }
}
