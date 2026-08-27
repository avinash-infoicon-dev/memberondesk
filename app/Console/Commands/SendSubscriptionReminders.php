<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class SendSubscriptionReminders extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'Expire overdue memberships and queue WhatsApp reminders';

    public function handle(SubscriptionService $subscriptions, WhatsAppService $whatsApp): int
    {
        $expired = $subscriptions->expireOverdue();
        $queued = $whatsApp->dispatchDueReminders();

        $this->info("Expired {$expired} subscriptions and queued {$queued} WhatsApp reminders.");

        return self::SUCCESS;
    }
}
