<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = ['automatic_trade_sync_enabled'];

    protected function casts(): array
    {
        return ['automatic_trade_sync_enabled' => 'boolean'];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'automatic_trade_sync_enabled' => true,
        ]);
    }

    public static function automaticTradeSyncEnabled(): bool
    {
        return static::current()->automatic_trade_sync_enabled;
    }
}
