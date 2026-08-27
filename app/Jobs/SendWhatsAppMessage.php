<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWhatsAppMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $notificationLogId) {}

    public function handle(WhatsAppService $whatsApp): void
    {
        $log = NotificationLog::withoutGlobalScopes()->find($this->notificationLogId);

        if (! $log) {
            return;
        }

        $whatsApp->sendQueued($log);
    }
}
