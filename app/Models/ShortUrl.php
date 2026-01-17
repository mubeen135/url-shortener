<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'short_code',
        'long_url',
        'hits',
        'hits_this_month',
        'hits_last_month',
        'hits_last_week',
        'hits_today',
    ];

    protected $appends = [
        'short_url',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getShortUrlAttribute(): string
    {
        return url('/s/' . $this->short_code);
    }

    public function incrementHits(): void
    {
        $this->increment('hits');
        $this->increment('hits_today');
        $this->increment('hits_this_month');
        
        // Note: You would need to implement a scheduled task to reset daily/weekly/monthly stats
    }
}