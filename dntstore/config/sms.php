<?php

return [
    'driver' => env('SMS_DRIVER', 'log'),
    'sender' => env('SMS_SENDER', 'DNTStore'),
    'otp_template' => env('SMS_OTP_TEMPLATE', 'Ma OTP cua ban la :code. Hieu luc :ttl phut.'),
    'http' => [
        'url' => env('SMS_HTTP_URL'),
        'token' => env('SMS_HTTP_TOKEN'),
        'timeout' => (int)env('SMS_HTTP_TIMEOUT', 10),
        'headers' => [
            'Accept' => 'application/json',
        ],
    ],
];
