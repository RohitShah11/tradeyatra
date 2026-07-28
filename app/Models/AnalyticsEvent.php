<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = ['visitor_id', 'user_id', 'event', 'route', 'path', 'referrer', 'source', 'medium', 'campaign', 'device_type', 'browser', 'operating_system', 'country_code', 'country', 'region', 'city', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
