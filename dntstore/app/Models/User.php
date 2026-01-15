<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'phone',
        'address',
        'city',
        'district',
        'ward',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'status' => 'string',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Check if user is locked
     */
    public function isLocked(): bool
    {
        return $this->status === 'locked' || $this->is_blocked;
    }

    /**
     * Lock the user account
     */
    public function lockAccount(?string $reason = null, ?int $by = null): void
    {
        $this->forceFill(['status' => 'locked'])->save();
    }

    /**
     * Unlock the user account
     */
    public function unlockAccount(): void
    {
        $this->forceFill(['status' => 'active'])->save();
    }

    /**
     * Lock the user
     */
    public function lock(): void
    {
        $this->update(['status' => 'locked']);
    }

    /**
     * Unlock the user
     */
    public function unlock(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Scope for locked users
     */
    public function scopeLocked($query)
    {
        return $query->where('status', 'locked');
    }

    /**
     * Scope for not locked users
     */
    public function scopeNotLocked($query)
    {
        return $query->where('status', '!=', 'locked');
    }
}
