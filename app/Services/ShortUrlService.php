<?php

namespace App\Services;

use App\Models\ShortUrl;
use Illuminate\Support\Str;

class ShortUrlService
{
    public function generateUniqueCode($length = 8): string
    {
        do {
            $code = Str::random($length);
        } while (ShortUrl::where('short_code', $code)->exists());

        return $code;
    }

    public function updateStats(): void
    {
        // This would be called by a scheduled task to reset daily/weekly/monthly stats
        // For simplicity, we'll implement basic logic
        
        $today = now()->format('Y-m-d');
        
        // Reset today's hits for all URLs
        ShortUrl::where('hits_today', '>', 0)->update(['hits_today' => 0]);
        
        // Update last week's hits (simplified - you'd want more sophisticated logic)
        if (now()->dayOfWeek === 1) { // Monday
            ShortUrl::query()->update([
                'hits_last_week' => \DB::raw('hits_this_week'),
                'hits_this_week' => 0,
            ]);
        }
        
        // Update last month's hits
        if (now()->day === 1) { // First day of month
            ShortUrl::query()->update([
                'hits_last_month' => \DB::raw('hits_this_month'),
                'hits_this_month' => 0,
            ]);
        }
    }
}