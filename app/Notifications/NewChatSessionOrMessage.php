<?php

namespace App\Notifications;

use App\Models\ChatSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Str;

class NewChatSessionOrMessage extends Notification
{
    use Queueable;

    public function __construct(
        protected ChatSession $session,
        protected ?string $message = null
    ) {
    }

    public function via($notifiable)
    {
        $channels = ['database'];

        if (config('broadcasting.default') !== 'null') {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    public function toDatabase($notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    protected function payload(): array
    {
        $name = $this->session->guest_name ?: ($this->session->user?->name ?? 'Khách vãng lai');
        $contactParts = array_filter([
            $this->session->guest_email,
            $this->session->guest_phone,
        ]);
        $contact = $contactParts ? implode(' • ', $contactParts) : 'N/A';
        $snippet = $this->message ? Str::limit(trim($this->message), 140) : null;

        $title = 'Tin nhắn mới từ khách';
        $message = $snippet
            ? "{$name} ({$contact}): {$snippet}"
            : "{$name} ({$contact}) vừa gửi tin nhắn mới.";

        return [
            'title' => $title,
            'message' => $message,
            'type' => 'chat_new_message',
            'session_id' => $this->session->session_id,
            'session_db_id' => $this->session->id,
            'url' => route('admin.chat-sessions.show', $this->session->id),
        ];
    }
}
