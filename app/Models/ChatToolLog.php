<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatToolLog extends Model
{
    use HasFactory;

    protected $table = 'chat_tools_log';

    protected $fillable = [
        'chat_session_id',
        'type',
        'status',
        'input',
        'output',
        'meta',
    ];

    protected $casts = [
        'input' => 'array',
        'output' => 'array',
        'meta' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }
}
