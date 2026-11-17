<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request)
    {
        if (! $request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        if ($this->expired($request)) {
            $this->forget($request);

            return redirect()->route('login')->withErrors(['email' => __('messages.2fa_expired')]);
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        if (! $request->session()->has('2fa_user_id') || $this->expired($request)) {
            $this->forget($request);

            return redirect()->route('login')->withErrors(['email' => __('messages.2fa_expired')]);
        }

        $user = User::find($request->session()->get('2fa_user_id'));

        if (! $user || ! $user->is_active || ! $user->hasTwoFactorEnabled()) {
            $this->forget($request);

            throw ValidationException::withMessages(['code' => __('auth.failed')]);
        }

        $code = preg_replace('/\s+/', '', $data['code']);

        $valid = preg_match('/^\d{6}$/', $code) === 1
            ? $user->verifyTwoFactorCode($code)
            : $user->useRecoveryCode($code);

        if (! $valid) {
            throw ValidationException::withMessages(['code' => __('messages.2fa_invalid_code')]);
        }

        $remember = (bool) $request->session()->get('2fa_remember', false);

        $this->forget($request);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        activity()->causedBy($user)->log('تسجيل دخول');

        return redirect()->intended(route('dashboard'));
    }

    protected function expired(Request $request): bool
    {
        $startedAt = (int) $request->session()->get('2fa_started_at', 0);

        return $startedAt === 0 || (time() - $startedAt) > 300;
    }

    protected function forget(Request $request): void
    {
        $request->session()->forget(['2fa_user_id', '2fa_remember', '2fa_started_at']);
    }
}
