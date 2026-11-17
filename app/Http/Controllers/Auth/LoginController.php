<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (! Auth::user()->is_active) {
                Auth::logout();

                throw ValidationException::withMessages(['email' => __('auth.failed')]);
            }

            if (Auth::user()->hasTwoFactorEnabled()) {
                $user = Auth::user();
                Auth::logout();
                $request->session()->regenerate();
                $request->session()->put([
                    '2fa_user_id' => $user->id,
                    '2fa_remember' => $request->boolean('remember'),
                    '2fa_started_at' => time(),
                ]);

                return redirect()->route('two-factor.login');
            }

            $request->session()->regenerate();

            // Update last login
            Auth::user()->update(['last_login_at' => now()]);

            // Log activity
            activity()
                ->causedBy(Auth::user())
                ->log('تسجيل دخول');

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }
}
