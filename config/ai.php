<?php

return [
    'provider' => env('AI_PROVIDER', env('GEMINI_API_KEY') ? 'gemini' : 'openai'),
    'api_key' => env('AI_API_KEY', env('GEMINI_API_KEY')),
    'model' => env('AI_MODEL', env('GEMINI_MODEL', 'gpt-4o-mini')),
];

