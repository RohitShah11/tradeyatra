<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SharkAccount extends Model
{
    protected $hidden = ['api_key', 'api_secret'];

    protected $fillable = [
        'user_id',
        'name',
        'api_key',
        'api_secret',
        'base_url',
        'public_base_url',
        'default_symbol',
        'margin_asset',
        'is_active',
        'auto_sync_enabled',
        'last_sync_started_at',
        'last_synced_at',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'api_secret' => 'encrypted',
        'is_active' => 'boolean',
        'auto_sync_enabled' => 'boolean',
        'last_sync_started_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function active(?int $userId = null): ?self
    {
        return static::query()
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->where('is_active', true)
            ->latest()
            ->first();
    }
}
