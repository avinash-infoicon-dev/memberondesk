<?php

return [
    'currency' => 'INR',
    'default_gateway' => env('PAYMENT_GATEWAY', 'razorpay'),
    'payment_request_ttl_hours' => (int) env('PAYMENT_REQUEST_TTL_HOURS', 48),
    'razorpay' => [
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],
];
