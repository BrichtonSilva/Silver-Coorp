<?php

namespace App\Http\Controllers\Access;

use App\Http\Requests\Access\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin(): View
    {
        return view('access.login');
    }

    /**
     * Handle a login request with rate limiting (max 5 attempts per 60s).
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $login = trim($request->input('login'));
        $password = $request->input('password');
        $key = 'login:' . strtolower($login) . ':' . $request->ip();

        // Rate limiting: 5 attempts per 60 seconds
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->withInput($request->only('login'))
                ->with('error', "Trop de tentatives. Réessayez dans {$seconds} secondes.");
        }

        $user = User::findByLogin($login);

        // Verify credentials
        if (!$user || !password_verify($password, $user->password)) {
            RateLimiter::hit($key, 60);

            return back()
                ->withInput($request->only('login'))
                ->with('error', 'Identifiant ou mot de passe incorrect.');
        }

        // Check active account — R04: same error message for all failures
        if (!$user->is_active) {
            RateLimiter::hit($key, 60);

            return back()
                ->withInput($request->only('login'))
                ->with('error', 'Identifiant ou mot de passe incorrect.');
        }

        // Login successful
        RateLimiter::clear($key);
        Auth::login($user, $request->boolean('remember'));

        // Regenerate session to prevent fixation
        $request->session()->regenerate(true);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Invalidate the session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
