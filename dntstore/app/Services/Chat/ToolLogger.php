<?php

namespace App\Services\Chat;

use App\Models\ChatSession;
use App\Models\ChatToolLog;

class ToolLogger
{
    public function log(
        ?ChatSession $session,
        string $type,
        array $input = [],
        array $output = [],
        string $status = 'success',
        array $meta = []
    ): void {
        try {
            ChatToolLog::create([
                'chat_session_id' => $session?->id,
                'type' => $type,
                'status' => $status,
                'input' => $input,
                'output' => $output,
                'meta' => $meta,
            ]);
        } catch (\Throwable $e) {
            // avoid breaking chat flow if logging fails
        }
    }
}
