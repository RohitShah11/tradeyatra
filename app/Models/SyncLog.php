<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    protected $fillable = [
        'user_id',
        'shark_account_id',
        'delta_account_id',
        'status',
        'message',
        'imported_count',
        'orders_count',
        'positions_count',
        'wallet_snapshot',
        'raw_payload',
    ];

    protected $casts = [
        'wallet_snapshot' => 'array',
        'raw_payload' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(SharkAccount::class, 'shark_account_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deltaAccount(): BelongsTo
    {
        return $this->belongsTo(DeltaAccount::class);
    }
}
