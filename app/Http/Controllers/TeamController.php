<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        // $user = Auth::user();
        
        // if (!$user->isAdmin()) {
        //     abort(403);
        // }

        // $teamMembers = User::where('company_id', $user->company_id)
        //     ->where('id', '!=', $user->id)
        //     ->withCount('shortUrls')
        //     ->withSum('shortUrls', 'hits')
        //     ->paginate(10);

        // return response()->json($teamMembers);

        $user = Auth::user();

        if (!$user->isAdmin()) {
             abort(403);
        }
    
        // Get all team members for the user's company
        $teamMembers = User::where('company_id', $user->company_id)
            ->withCount(['shortUrls'])
            ->withSum('shortUrls as short_urls_sum_hits', 'hits')
            ->orderBy('created_at', 'desc')
            ->paginate(20); // More per page for "View All"
        
        return view('client-admin.team.index', compact('teamMembers'));
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $teamMember = User::findOrFail($id);

        if (!$user->isAdmin() || $teamMember->company_id != $user->company_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Prevent deleting yourself
        if ($teamMember->id == $user->id) {
            return response()->json(['error' => 'Cannot delete yourself'], 400);
        }

        $teamMember->delete();

        return response()->json(['success' => true]);
    }
}