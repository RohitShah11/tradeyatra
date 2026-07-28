<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportContribution extends Model
{
    protected $fillable = [
        'user_id', 'contributor_name', 'email', 'phone', 'amount', 'transaction_reference', 'message',
        'show_publicly', 'anonymous', 'status', 'verified_by', 'verified_at', 'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'show_publicly' => 'boolean',
            'anonymous' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'verified_by');
    }
}
