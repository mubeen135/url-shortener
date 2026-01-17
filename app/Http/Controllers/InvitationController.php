<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\InvitationEmail;
use Carbon\Carbon;

class InvitationController extends Controller
{
    public function inviteClient(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            //echo "here"; exit;
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies,email|unique:users,email',
        ]);

        // Create new company
        $company = Company::create([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Create invitation token
        $token = Str::random(60);
        
        $invitation = Invitation::create([
            'company_id' => $company->id,
            'invited_by' => $user->id,
            'email' => $request->email,
            'token' => $token,
            'role' => 'admin',
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        // Send invitation email
        // Mail::to($request->email)->send(new InvitationEmail($invitation, true));

        return response()->json([
            'success' => true,
            'message' => 'Invitation sent successfully',
        ]);
    }

    public function inviteTeamMember(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,member',
        ]);

        // Create invitation token
        $token = Str::random(60);
        
        $invitation = Invitation::create([
            'company_id' => $user->company_id,
            'invited_by' => $user->id,
            'email' => $request->email,
            'token' => $token,
            'role' => $request->role,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        // Send invitation email
        Mail::to($request->email)->send(new InvitationEmail($invitation, false));

        return response()->json([
            'success' => true,
            'message' => 'Invitation sent successfully',
        ]);
    }

    public function acceptInvitation($token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', Carbon::now())
            ->firstOrFail();

        // Show registration form
        return view('auth.register', compact('invitation'));
    }

    public function completeRegistration(Request $request, $token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', Carbon::now())
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create user
        $user = User::create([
            'company_id' => $invitation->company_id,
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => bcrypt($request->password),
            'role' => $invitation->role,
            'email_verified_at' => Carbon::now(),
        ]);

        // Mark invitation as accepted
        $invitation->markAsAccepted();

        // Auto login
        Auth::login($user);

        return redirect('/dashboard');
    }
}