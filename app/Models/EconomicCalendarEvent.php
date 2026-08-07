<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EconomicCalendarEvent extends Model
{
    protected $fillable = [
        'provider', 'external_id', 'title', 'currency', 'country', 'impact',
        'scheduled_at', 'actual', 'forecast', 'previous', 'url', 'payload',
    ];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'payload' => 'array'];
    }
}
