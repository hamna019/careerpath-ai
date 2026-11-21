<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    public const SESSION_TIMEOUT_SECONDS = 90;

    protected $fillable = [
        'user_id',
        'email',
        'role',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'status',
        'failure_reason',
        'login_at',
        'last_seen_at',
        'logout_at',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function findOpenFor(int $userId)
    {
        return static::where('user_id', $userId)
            ->where('status', 'success')
            ->whereNull('logout_at')
            ->latest('login_at')
            ->first();
    }

    public function isStale(): bool
    {
        $lastSeen = $this->last_seen_at ?? $this->login_at;
        return $lastSeen->diffInSeconds(now()) > self::SESSION_TIMEOUT_SECONDS;
    }
}