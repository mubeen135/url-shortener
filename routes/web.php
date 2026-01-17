<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShortUrlController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\RedirectController;

// Public routes
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public URL redirection
Route::get('s/{code}', [RedirectController::class, 'redirect']);

// Invitation acceptance
Route::get('/invitation/{token}', [InvitationController::class, 'acceptInvitation'])
    ->name('invitation.accept');
Route::post('/invitation/{token}/complete', [InvitationController::class, 'completeRegistration'])
    ->name('invitation.complete');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Super Admin routes
    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('/clients', [DashboardController::class, 'clients'])->name('clients');
        Route::post('/clients/invite', [InvitationController::class, 'inviteClient'])->name('clients.invite');
        Route::get('/super-admin/short-urls', [ShortUrlController::class, 'superAdminIndex'])->name('short-urls.index');
    });
    
    // Short URL routes
    Route::middleware(['role:admin,member'])->group(function () {
        Route::post('/short-urls', [ShortUrlController::class, 'store'])->name('short-urls.store');
        Route::get('/short-urls/data', [ShortUrlController::class, 'index'])->name('short-urls.data');
        Route::delete('/short-urls/{id}', [ShortUrlController::class, 'destroy'])->name('short-urls.destroy');
        Route::get('/short-urls/export', [ShortUrlController::class, 'export'])->name('short-urls.export');
        Route::get('/short-urls/export-member', [ShortUrlController::class, 'exportMember'])->name('short-urls.export-member');
    });
    
    // Admin routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/team', [TeamController::class, 'index'])->name('team.index');
        Route::delete('/team/{id}', [TeamController::class, 'destroy'])->name('team.destroy');
        Route::post('/invite/team', [InvitationController::class, 'inviteTeamMember'])->name('invite.team');
    });
});