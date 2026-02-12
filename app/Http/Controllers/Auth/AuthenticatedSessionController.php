<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'user_logged_in',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'before' => null,
                'after' => null,
            ]);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'user_logged_out',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'before' => null,
                'after' => null,
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

