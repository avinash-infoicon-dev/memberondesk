<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case Whatsapp = 'whatsapp';
    case Email = 'email';
    case Sms = 'sms';
}
