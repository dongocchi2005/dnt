<?php

namespace App\Notifications;

use App\Models\ChatSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ChatHandoverNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected ChatSession $session,
        protected ?string $userMessage = null
    ) {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $name = $this->session->guest_name ?: ($this->session->user?->name ?? 'Khách vãng lai');
        $phone = $this->session->guest_phone ?: 'N/A';
        $snippet = $this->userMessage ? Str::limit(trim($this->userMessage), 120) : null;

        return [
            'title' => 'Yêu cầu gặp admin',
            'message' => $snippet
                ? "Khách {$name} ({$phone}) yêu cầu hỗ trợ: {$snippet}"
                : "Khách {$name} ({$phone}) yêu cầu gặp admin.",
            'type' => 'chat_handover',
            'session_id' => $this->session->session_id,
            'url' => route('admin.chat-sessions.show', $this->session->id),
        ];
    }
}
