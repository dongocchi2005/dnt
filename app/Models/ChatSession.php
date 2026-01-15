<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ChatToolLog;
use App\Models\User;
 use App\Models\ChatSessionRead;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'title',
        'user_id',
        'last_intent',
        'context',
        'guest_name',
        'guest_phone',
        'guest_email',
        'user_name',
        'user_phone',
        'user_email',
        'status',
        'closed_at',
        'assigned_admin_id',
        'assigned_at',
        'last_message_at',
        'last_handled_by',
        'conversion_type',
        'converted_at',
        'last_activity',
        'last_staff_message_at',
        'last_customer_message_at',
        'pending_close_at',
        'pending_close_reason',
        'waiting_customer_reply',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'last_message_at' => 'datetime',
        'assigned_at' => 'datetime',
        'context' => 'array',
        'converted_at' => 'datetime',
        'closed_at' => 'datetime',
        'last_staff_message_at' => 'datetime',
        'last_customer_message_at' => 'datetime',
        'pending_close_at' => 'datetime',
        'waiting_customer_reply' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function reads()
    {
        return $this->hasMany(ChatSessionRead::class, 'chat_session_id');
    }

    public function toolLogs()
    {
        return $this->hasMany(ChatToolLog::class, 'chat_session_id');
    }

    public function isActive()
    {
        return !in_array($this->status, ['closed', 'expired'], true);
    }

    public function isPending()
    {
        return in_array($this->status, ['pending', 'active', 'handed_over'], true);
    }

    public function isAssigned()
    {
        return $this->status === 'assigned';
    }

    public function isHandedOver()
    {
        return $this->status === 'assigned' || $this->last_handled_by === 'admin';
    }

    public function isClosed()
    {
        return in_array($this->status, ['closed', 'expired'], true);
    }

    public function shouldSuppressBot()
    {
        return $this->isAssigned() || $this->last_handled_by === 'admin';
    }

    public function markHandedOver()
    {
        $this->update([
            'status' => 'pending',
            'last_handled_by' => 'admin',
        ]);
    }

    public function assignToAdmin(int $adminId): void
    {
        $this->update([
            'status' => 'assigned',
            'assigned_admin_id' => $adminId,
            'assigned_at' => now(),
            'last_handled_by' => 'admin',
        ]);
    }

    public function close()
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
            'waiting_customer_reply' => false,
            'pending_close_at' => null,
        ]);
    }

    public function getGuestNameAttribute($value)
    {
        return $value ?: $this->user_name;
    }

    public function getGuestEmailAttribute($value)
    {
        return $value ?: $this->user_email;
    }

    public function getGuestPhoneAttribute($value)
    {
        return $value ?: $this->user_phone;
    }

    public function getRouteKeyName()
    {
        return 'session_id';
    }
}
