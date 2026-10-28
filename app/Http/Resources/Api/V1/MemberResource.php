<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->displayStatus();
        [$current, $previous, $nameFallback] = $this->currentAndPreviousSubscriptions();

        return [
            'id' => $this->id,
            'member_code' => $this->member_code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_ciphertext' => $this->phone_ciphertext,
            'phone_wrapped_key' => $this->phone_wrapped_key,
            'gender' => $this->gender,
            'address' => $this->address,
            'profile_image_url' => $this->profile_image_url,
            'status' => $status->value,
            'status_label' => $status->label(),
            'account_status' => $this->status,
            'joined_at' => $this->joined_at?->toDateString(),
            'whatsapp_welcome_message' => $this->whatsapp_welcome_message,
            'whatsapp_welcome_sent' => $this->whatsapp_welcome_sent_at !== null,
            'qr_token' => $this->whenLoaded('activeQrCode', fn () => $this->activeQrCode?->token),
            'current_plan_name' => $this->when(
                $this->subscriptionRelationsLoaded(),
                fn () => $current?->plan?->name ?? $nameFallback?->plan?->name
            ),
            'last_active_plan_name' => $this->when(
                $this->relationLoaded('activatedSubscriptions'),
                fn () => $previous?->plan?->name
            ),
            'active_subscription' => $this->when(
                $this->subscriptionRelationsLoaded(),
                fn () => $this->subscriptionPayload($current)
            ),
            'last_active_subscription' => $this->when(
                $this->relationLoaded('activatedSubscriptions'),
                fn () => $this->subscriptionPayload($previous)
            ),
        ];
    }

    /**
     * @return array{0: ?Subscription, 1: ?Subscription, 2: ?Subscription}
     */
    private function currentAndPreviousSubscriptions(): array
    {
        /** @var Collection<int, Subscription> $activated */
        $activated = $this->relationLoaded('activatedSubscriptions')
            ? $this->activatedSubscriptions
            : collect();

        $current = $this->relationLoaded('latestActiveSubscription')
            ? $this->latestActiveSubscription
            : null;

        if (! $current && $this->relationLoaded('activeSubscription')) {
            $current = $this->activeSubscription;
        }

        if ($current) {
            $previous = $activated->first(fn (Subscription $row) => $row->id !== $current->id);
            $nameFallback = null;
        } else {
            // No Active row: newest activated is name-only fallback; previous is second-newest.
            $nameFallback = $activated->first();
            $previous = $activated->skip(1)->first();
        }

        return [$current, $previous, $nameFallback];
    }

    private function subscriptionRelationsLoaded(): bool
    {
        return $this->relationLoaded('latestActiveSubscription')
            || $this->relationLoaded('activeSubscription')
            || $this->relationLoaded('activatedSubscriptions');
    }

    private function subscriptionPayload(?Subscription $subscription): ?array
    {
        if (! $subscription) {
            return null;
        }

        return [
            'id' => $subscription->id,
            'status' => $subscription->status,
            'starts_at' => $subscription->starts_at?->toDateString(),
            'ends_at' => $subscription->ends_at?->toDateString(),
            'plan_id' => $subscription->membership_plan_id,
            'plan_name' => $subscription->plan?->name,
        ];
    }
}
