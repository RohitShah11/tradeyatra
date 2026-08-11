<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPageSession extends Model
{
    protected $fillable = [
        'user_activity_session_id', 'user_id', 'route', 'path', 'active_seconds', 'idle_seconds',
        'started_at', 'last_seen_at', 'ended_at',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'last_seen_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function activitySession(): BelongsTo
    {
        return $this->belongsTo(UserActivitySession::class, 'user_activity_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
