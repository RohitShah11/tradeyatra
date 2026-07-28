<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'time',
        'pair',
        'asset_class',
        'market_segment',
        'currency',
        'trade_type',
        'strategy',
        'market_condition',
        'lot_size',
        'risk_amount',
        'profit',
        'loss',
        'trading_fees',
        'current_balance',
        'emotion',
        'screenshot',
        'broker',
        'exchange',
        'external_trade_id',
        'external_order_id',
        'exchange_payload',
        'notes',
        'entry_price',
        'exit_price',
        'quantity',
        'leverage',
        'status',
        'setup_quality',
        'mistake_tags',
        'exit_reason',
        'plan_followed',
        'shark_order_id',
        'shark_trade_id',
        'shark_position_id',
        'shark_payload',
        'imported_at',
    ];

    protected $casts = [
        'date' => 'date',
        'mistake_tags' => 'array',
        'shark_payload' => 'array',
        'exchange_payload' => 'array',
        'plan_followed' => 'boolean',
        'imported_at' => 'datetime',
    ];

    public function getNetPnlAttribute(): float
    {
        if ($this->broker === 'SharkExchange') {
            return (float) ($this->profit ?? 0) - (float) ($this->loss ?? 0);
        }

        return (float) ($this->profit ?? 0) - (float) ($this->loss ?? 0) - (float) ($this->trading_fees ?? 0);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
