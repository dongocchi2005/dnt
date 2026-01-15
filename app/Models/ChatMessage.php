<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_session_id',
        'role',
        'content',
        'meta',
        'idempotency_key',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }

    public function isFromUser()
    {
        return $this->role === 'user';
    }

    public function isFromAssistant()
    {
        return $this->role === 'assistant' || $this->role === 'bot';
    }

    public function isFromAdmin()
    {
        return $this->role === 'admin';
    }

    public function isFromBot()
    {
        return $this->role === 'bot';
    }
}
