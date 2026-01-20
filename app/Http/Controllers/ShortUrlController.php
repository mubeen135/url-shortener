<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\ShortUrl;
use App\Services\ShortUrlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ShortUrlController extends Controller
{
    protected $shortUrlService;

    public function __construct(ShortUrlService $shortUrlService)
    {
        $this->shortUrlService = $shortUrlService;
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->canCreateShortUrls()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'long_url' => 'required|url|max:2048',
        ]);

        $shortCode = $this->shortUrlService->generateUniqueCode();
        
        $shortUrl = ShortUrl::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'short_code' => $shortCode,
            'long_url' => $request->long_url,
        ]);

        return response()->json([
            'success' => true,
            'short_url' => $shortUrl->short_url,
            'short_code' => $shortCode,
        ]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        if($user->isMember()){
            $query = $user->shortUrls()
            ->orderBy('created_at', 'desc');
        }else{
            $query = ShortUrl::where('company_id', $user->company_id)
            ->with('user')
            ->orderBy('created_at', 'desc');
        }
        
        // Start query
        
        
        // Apply date filters
        $filter = $request->get('filter', 'all');
        
        switch ($filter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
                
            case 'last_week':
                $query->whereBetween('created_at', [
                    Carbon::now()->subWeek(),
                    Carbon::now()
                ]);
                break;
                
            case 'this_month':
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
                break;
                
            case 'last_month':
                $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
                    ->whereYear('created_at', Carbon::now()->subMonth()->year);
                break;
                
            case 'custom':
                $startDate = $request->get('start_date');
                $endDate = $request->get('end_date');
                
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                }
                break;
        }
        
        // Paginate results
        $shortUrls = $query->paginate(5);
        
        // Get stats (apply same filters for stats if needed)
        $totalUrls = $query->count();
        $totalHits = ShortUrl::where('company_id', $user->company_id)
            ->when($filter != 'all', function ($q) use ($filter, $request) {
                $this->applyDateFilter($q, $filter, $request);
            })
            ->sum('hits');
        
        $activeUsers = User::where('company_id', $user->company_id)->count();
        
        return view('client-admin.short-urls.index', compact('shortUrls', 'totalUrls', 'totalHits', 'activeUsers'));
    }

    // Helper method for date filtering
    private function applyDateFilter($query, $filter, $request)
    {
        switch ($filter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
                
            case 'last_week':
                $query->whereBetween('created_at', [
                    Carbon::now()->subWeek(),
                    Carbon::now()
                ]);
                break;
                
            case 'this_month':
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
                break;
                
            case 'last_month':
                $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
                    ->whereYear('created_at', Carbon::now()->subMonth()->year);
                break;
                
            case 'custom':
                $startDate = $request->get('start_date');
                $endDate = $request->get('end_date');
                
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                }
                break;
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $shortUrl = ShortUrl::findOrFail($id);
        
        // Check authorization
        if ($user->isSuperAdmin() || 
            ($user->isAdmin() && $shortUrl->company_id == $user->company_id) ||
            ($user->isMember() && $shortUrl->user_id == $user->id)) {
            
            $shortUrl->delete();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function superAdminIndex(Request $request)
    {
        // Only super admin can access this
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }
        
        // Get all short URLs with company and user info
        $query = ShortUrl::with(['company', 'user'])
            ->orderBy('created_at', 'desc');
        
        // Add date filter if provided
        if ($request->has('filter')) {
            $now = Carbon::now();
            
            switch($request->filter) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'last_week':
                    $query->whereBetween('created_at', [
                        $now->copy()->subWeek()->startOfWeek(),
                        $now->copy()->subWeek()->endOfWeek()
                    ]);
                    break;
                case 'last_month':
                    $query->whereBetween('created_at', [
                        $now->copy()->subMonth()->startOfMonth(),
                        $now->copy()->subMonth()->endOfMonth()
                    ]);
                    break;
                case 'this_month':
                    $query->whereBetween('created_at', [
                        $now->copy()->startOfMonth(),
                        $now->copy()->endOfMonth()
                    ]);
                    break;
            }
        }
        
        // Always use pagination (remove "View All" check)
        $shortUrls = $query->paginate(perPage: 5);
        
        
        
        return view('dashboard.superadmin-shorturls', compact(
            'shortUrls'
        ));
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        
        // Start query
        $query = ShortUrl::where('company_id', $user->company_id)
            ->with('user')
            ->orderBy('created_at', 'desc');
        
        // Apply filters (same as index method)
        $filter = $request->get('filter', 'all');
        $this->applyDateFilter($query, $filter, $request);
        
        $shortUrls = $query->get();
        
        $fileName = 'short_urls_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $callback = function() use ($shortUrls) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'Short URL',
                'Short Code',
                'Long URL',
                'Hits',
                'Created By',
                'Created At'
            ]);
            
            // Add data rows
            foreach ($shortUrls as $url) {
                fputcsv($file, [
                    $url->short_url,
                    $url->short_code,
                    $url->long_url,
                    $url->hits,
                    $url->user->name,
                    $url->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function exportMember(Request $request)
    {
        $user = Auth::user();
        
        // Start query for current user only
        $query = $user->shortUrls()
            ->orderBy('created_at', 'desc');
        
        // Apply filters
        $filter = $request->get('filter', 'all');
        
        switch ($filter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'last_week':
                $query->whereBetween('created_at', [
                    Carbon::now()->subWeek(),
                    Carbon::now()
                ]);
                break;
            case 'this_month':
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'last_month':
                $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
                    ->whereYear('created_at', Carbon::now()->subMonth()->year);
                break;
        }
        
        $shortUrls = $query->get();
        
        $fileName = 'my_short_urls_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $callback = function() use ($shortUrls) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'Short URL',
                'Short Code',
                'Long URL',
                'Hits',
                'Created At'
            ]);
            
            // Add data rows
            foreach ($shortUrls as $url) {
                fputcsv($file, [
                    $url->short_url,
                    $url->short_code,
                    $url->long_url,
                    $url->hits,
                    $url->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}