<?php

return [
    'length' => (int)env('OTP_LENGTH', 6),
    'ttl_minutes' => (int)env('OTP_TTL_MINUTES', 5),
    'cooldown_seconds' => (int)env('OTP_COOLDOWN_SECONDS', 60),
    'max_attempts' => (int)env('OTP_MAX_ATTEMPTS', 5),
];
