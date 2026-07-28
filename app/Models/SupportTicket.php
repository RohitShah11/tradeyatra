<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_number', 'user_id', 'assigned_admin_id', 'subject', 'category', 'priority', 'status',
        'user_unread_count', 'admin_unread_count', 'admin_notes', 'last_replied_at', 'last_replied_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['last_replied_at' => 'datetime', 'resolved_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_admin_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }
}
