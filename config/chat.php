<?php

return [
    'staff_reply_timeout_seconds' => (int) env('CHAT_STAFF_REPLY_TIMEOUT_SECONDS', 900),
    'auto_close_countdown_seconds' => (int) env('CHAT_AUTO_CLOSE_COUNTDOWN_SECONDS', 60),
];

