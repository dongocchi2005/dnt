<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public ChatMessage $message)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('chat-session.' . $this->message->chat_session_id);
    }

    public function broadcastAs(): string
    {
        return 'chat.message.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'chat_session_id' => $this->message->chat_session_id,
            'role' => $this->message->role,
            'content' => $this->message->content,
            'meta' => $this->message->meta,
            'created_at' => $this->message->created_at?->toISOString(),
        ];
    }
}
