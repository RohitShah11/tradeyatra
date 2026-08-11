<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserActivitySession extends Model
{
    protected $fillable = [
        'user_id', 'session_key', 'current_route', 'current_path', 'active_seconds', 'idle_seconds',
        'started_at', 'last_seen_at', 'last_interacted_at', 'ended_at',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'last_seen_at' => 'datetime', 'last_interacted_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(UserPageSession::class);
    }

    public function presenceStatus(): string
    {
        if (! $this->last_seen_at || $this->last_seen_at->lt(now()->subSeconds(90))) {
            return 'offline';
        }

        if (! $this->last_interacted_at || $this->last_interacted_at->lt(now()->subMinutes(5))) {
            return 'idle';
        }

        return 'active';
    }
}
