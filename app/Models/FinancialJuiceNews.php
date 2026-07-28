<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialJuiceNews extends Model
{
    protected $table = 'financial_juice_news';

    protected $fillable = ['external_id', 'title', 'description', 'url', 'labels', 'published_at'];

    protected function casts(): array
    {
        return ['labels' => 'array', 'published_at' => 'datetime'];
    }
}
