<?php

namespace App\Listeners;

use App\Events\PaymentRecorded;
use App\Services\AuditService;

class LogPaymentRecorded
{
    public function __construct(private readonly AuditService $audit) {}

    public function handle(PaymentRecorded $event): void
    {
        $this->audit->log('payment.event_recorded', $event->payment);
    }
}
