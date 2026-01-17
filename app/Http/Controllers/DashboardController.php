<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard();
        } elseif ($user->isAdmin()) {
            return $this->adminDashboard();
        } else {
            return $this->memberDashboard();
        }
    }

    private function superAdminDashboard()
    {
        $user = Auth::user();
        
        // Check if "View All" is requested
        $showAllClients = request()->has('all');
        $showAllUrls = request()->has('all_urls');
        
        // Companies with pagination
        if ($showAllClients) {
            $companies = Company::withCount(['users', 'shortUrls'])
                ->withSum('shortUrls', 'hits')
                ->get(); // Get all without pagination
        } else {
            $companies = Company::withCount(['users', 'shortUrls'])
                ->withSum('shortUrls', 'hits')
                ->paginate(2);
        }
        
        // Short URLs with pagination
        if ($showAllUrls) {
            $shortUrls = ShortUrl::with(['company', 'user'])
                ->orderBy('created_at', 'desc')
                ->get(); // Get all without pagination
        } else {
            $shortUrls = ShortUrl::with(['company', 'user'])
                ->orderBy('created_at', 'desc')
                ->paginate(2);
        }
        
        return view('dashboard.superadmin', compact('companies', 'shortUrls', 'user', 'showAllClients', 'showAllUrls'));
    }

    private function adminDashboard()
    {
        $user = Auth::user();
        $company = $user->company;
        
        // Get all short URLs for the company
        $shortUrls = ShortUrl::where('company_id', $company->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Get team members
        $teamMembers = User::where('company_id', $company->id)
            ->where('id', '!=', $user->id)
            ->withCount('shortUrls')
            ->withSum('shortUrls', 'hits')
            ->paginate(10);
        
        // Get total stats
        $totalGenerated = $company->shortUrls()->count();
        $totalHits = $company->shortUrls()->sum('hits');
        
        return view('dashboard.admin', compact(
            'user', 'company', 'shortUrls', 'teamMembers', 'totalGenerated', 'totalHits'
        ));
    }

    private function memberDashboard()
    {
        $user = Auth::user();
        
        // Start query
        $query = $user->shortUrls()
            ->orderBy('created_at', 'desc');
        
        // Apply date filters
        $filter = request()->get('filter', 'all');
        
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
        
        // Paginate results
        $shortUrls = $query->paginate(10);
        
        // Get stats for this month, last month, last week, today
        $now = Carbon::now();
        
        $thisMonth = $user->shortUrls()
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();
        
        $lastMonth = $user->shortUrls()
            ->whereMonth('created_at', $now->subMonth()->month)
            ->whereYear('created_at', $now->year)
            ->count();
        
        $lastWeekStart = $now->copy()->subWeek()->startOfWeek();
        $lastWeekEnd = $now->copy()->subWeek()->endOfWeek();
        
        $lastWeek = $user->shortUrls()
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->count();
        
        $today = $user->shortUrls()
            ->whereDate('created_at', $now->today())
            ->count();
        
        return view('dashboard.member', compact(
            'user', 'shortUrls', 'thisMonth', 'lastMonth', 'lastWeek', 'today'
        ));
    }

    public function clients()
    {
        //echo "here"; exit;
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }
        
        $companies = Company::withCount(['users', 'shortUrls'])
            ->withSum('shortUrls', 'hits')
            ->paginate(10);
        
        return view('dashboard.clients', compact('companies'));
    }
}