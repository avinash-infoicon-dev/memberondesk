<?php

return [
    'driver' => env('WHATSAPP_DRIVER', 'log'),
    'from' => env('WHATSAPP_FROM', 'MemberOnDesk'),
    'meta' => [
        'token' => env('WHATSAPP_META_TOKEN'),
        'phone_number_id' => env('WHATSAPP_META_PHONE_NUMBER_ID'),
        'graph_version' => env('WHATSAPP_META_GRAPH_VERSION', 'v20.0'),
    ],
    'expiry_reminder_days' => (int) env('WHATSAPP_EXPIRY_REMINDER_DAYS', 3),
];
