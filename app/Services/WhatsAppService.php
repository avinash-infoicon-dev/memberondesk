<?php

namespace App\Services;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Enums\PaymentRequestStatus;
use App\Enums\SubscriptionStatus;
use App\Jobs\SendWhatsAppMessage;
use App\Models\Member;
use App\Models\NotificationLog;
use App\Models\PaymentRequest;
use App\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppService
{
    public function queueReminder(Member $member, NotificationType $type, string $message): NotificationLog
    {
        $log = NotificationLog::query()->create([
            'business_id' => $member->business_id,
            'member_id' => $member->id,
            'channel' => NotificationChannel::Whatsapp,
            'type' => $type,
            'to' => $member->phone,
            'message' => $message,
            'status' => NotificationStatus::Queued,
        ]);

        SendWhatsAppMessage::dispatch($log->id);

        return $log;
    }

    public function sendQueued(NotificationLog $log): NotificationLog
    {
        try {
            $this->deliver($log->to, $log->message);

            $log->update([
                'status' => NotificationStatus::Sent,
                'sent_at' => now(),
                'error' => null,
            ]);
        } catch (Throwable $e) {
            $log->update([
                'status' => NotificationStatus::Failed,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $log->fresh();
    }

    public function deliver(string $to, string $message): void
    {
        $driver = config('whatsapp.driver', 'log');

        if ($driver === 'meta') {
            $this->sendViaMeta($to, $message);

            return;
        }

        Log::info('WhatsApp message queued', [
            'to' => $to,
            'message' => $message,
        ]);
    }

    public function reminderMessage(Member $member, NotificationType $type, ?Subscription $subscription = null): string
    {
        $business = $member->business?->name ?? 'your membership';

        return match ($type) {
            NotificationType::PaymentPending => "Hi {$member->name}, a payment is pending for your {$business} membership. Please complete it to keep your access active.",
            NotificationType::SubscriptionExpiring => "Hi {$member->name}, your {$business} membership expires on {$subscription?->ends_at?->toDateString()}. Renew soon to avoid interruption.",
            NotificationType::SubscriptionExpired => "Hi {$member->name}, your {$business} membership expired on {$subscription?->ends_at?->toDateString()}. Please renew to continue.",
            NotificationType::Custom => "Hi {$member->name}, you have a new update from {$business}.",
        };
    }

    public function dispatchDueReminders(): int
    {
        $count = 0;
        $days = (int) config('whatsapp.expiry_reminder_days', 3);

        Subscription::withoutGlobalScopes()
            ->with(['member.business'])
            ->expiring($days)
            ->each(function (Subscription $subscription) use (&$count) {
                $member = $subscription->member;
                if (! $member?->phone || ! $member->business?->whatsapp_enabled) {
                    return;
                }

                if ($this->alreadySentToday($member, NotificationType::SubscriptionExpiring)) {
                    return;
                }

                $this->queueReminder(
                    $member,
                    NotificationType::SubscriptionExpiring,
                    $this->reminderMessage($member, NotificationType::SubscriptionExpiring, $subscription)
                );
                $count++;
            });

        Subscription::withoutGlobalScopes()
            ->with(['member.business'])
            ->where('status', SubscriptionStatus::Expired)
            ->whereDate('ends_at', now()->subDay()->toDateString())
            ->each(function (Subscription $subscription) use (&$count) {
                $member = $subscription->member;
                if (! $member?->phone || ! $member->business?->whatsapp_enabled) {
                    return;
                }

                if ($this->alreadySentToday($member, NotificationType::SubscriptionExpired)) {
                    return;
                }

                $this->queueReminder(
                    $member,
                    NotificationType::SubscriptionExpired,
                    $this->reminderMessage($member, NotificationType::SubscriptionExpired, $subscription)
                );
                $count++;
            });

        PaymentRequest::withoutGlobalScopes()
            ->with(['member.business'])
            ->where('status', PaymentRequestStatus::Pending)
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->each(function (PaymentRequest $request) use (&$count) {
                $member = $request->member;
                if (! $member?->phone || ! $member->business?->whatsapp_enabled) {
                    return;
                }

                if ($this->alreadySentToday($member, NotificationType::PaymentPending)) {
                    return;
                }

                $this->queueReminder(
                    $member,
                    NotificationType::PaymentPending,
                    $this->reminderMessage($member, NotificationType::PaymentPending, $request->subscription)
                );
                $count++;
            });

        return $count;
    }

    private function alreadySentToday(Member $member, NotificationType $type): bool
    {
        return NotificationLog::withoutGlobalScopes()
            ->where('member_id', $member->id)
            ->where('type', $type)
            ->whereDate('created_at', now())
            ->exists();
    }

    private function sendViaMeta(string $to, string $message): void
    {
        $token = config('whatsapp.meta.token');
        $phoneNumberId = config('whatsapp.meta.phone_number_id');
        $version = config('whatsapp.meta.graph_version', 'v20.0');

        if (! $token || ! $phoneNumberId) {
            throw new \RuntimeException('WhatsApp Meta credentials are not configured.');
        }

        $response = Http::withToken($token)
            ->post("https://graph.facebook.com/{$version}/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => preg_replace('/\D+/', '', $to),
                'type' => 'text',
                'text' => ['body' => $message],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('WhatsApp API error: '.$response->body());
        }
    }
}
