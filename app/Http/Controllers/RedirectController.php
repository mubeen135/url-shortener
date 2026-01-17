<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function redirect($code)
    {
        $shortUrl = ShortUrl::where('short_code', $code)->firstOrFail();
        //echo "<pre>"; print_r($shortUrl); exit;
        // Increment hit counters
        $shortUrl->increment('hits');
        
        // For weekly stats, you'd need to track the current week
        // This is simplified - you'd want to update this with a scheduled task
        
        return redirect($shortUrl->long_url);
    }
}