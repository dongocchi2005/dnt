<?php

namespace App\Services\Chat;

use App\Services\GeminiChatService;

class NluService
{
    public function __construct(protected GeminiChatService $gemini)
    {
    }

    public function analyze(array $history, string $message): array
    {
        $raw = $this->gemini->respond($history, $message);

        $intent = null;
        $slots = [];
        $confidence = null;
        $nextAction = null;

        try {
            $parsed = json_decode((string)$raw, true);
            if (is_array($parsed)) {
                $intent = $parsed['intent'] ?? null;
                $slots = is_array($parsed['slots'] ?? null) ? $parsed['slots'] : [];
                $confidence = $parsed['confidence'] ?? null;
                $nextAction = $parsed['next_action'] ?? null;
            }
        } catch (\Throwable $e) {
            $intent = null;
            $slots = [];
        }

        return [
            'raw' => $raw,
            'intent' => $intent,
            'slots' => $slots,
            'confidence' => $confidence,
            'next_action' => $nextAction,
        ];
    }
}
