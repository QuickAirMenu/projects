<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $qrSvg = null;
        $pendingSecret = null;

        if (filled($user->two_factor_secret) && ! $user->two_factor_confirmed_at) {
            $pendingSecret = $user->two_factor_secret;
            $qrSvg = $this->qrSvg($user->twoFactorOtpauthUrl());
        }

        return view('profile.two-factor', [
            'user' => $user,
            'qrSvg' => $qrSvg,
            'pendingSecret' => $pendingSecret,
        ]);
    }

    public function enable(Request $request)
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return back()->with('error', __('messages.2fa_already_enabled'));
        }

        $google2fa = new Google2FA;

        $user->forceFill([
            'two_factor_secret' => $google2fa->generateSecretKey(32),
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return redirect()->route('two-factor.index');
    }

    public function confirm(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if (! filled($user->two_factor_secret) || ! $user->verifyTwoFactorCode($data['code'])) {
            return back()->with('error', __('messages.2fa_invalid_code'));
        }

        $codes = $user->replaceRecoveryCodes();

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        activity()->causedBy($user)->log('two-factor enabled');

        return redirect()->route('two-factor.index')
            ->with('success', __('messages.2fa_enabled'))
            ->with('recovery_codes', $codes);
    }

    public function disable(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check($data['password'], $user->password)) {
            return back()->with('error', __('messages.2fa_wrong_password'));
        }

        if (! $user->verifyTwoFactorCode($data['code']) && ! $user->useRecoveryCode($data['code'])) {
            return back()->with('error', __('messages.2fa_invalid_code'));
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        activity()->causedBy($user)->log('two-factor disabled');

        return redirect()->route('two-factor.index')->with('success', __('messages.2fa_disabled'));
    }

    public function regenerateRecoveryCodes(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check($data['password'], $user->password)) {
            return back()->with('error', __('messages.2fa_wrong_password'));
        }

        if (! $user->hasTwoFactorEnabled()) {
            return back()->with('error', __('messages.2fa_not_enabled'));
        }

        $codes = $user->replaceRecoveryCodes();

        activity()->causedBy($user)->log('two-factor recovery codes regenerated');

        return redirect()->route('two-factor.index')
            ->with('success', __('messages.2fa_recovery_regenerated'))
            ->with('recovery_codes', $codes);
    }

    protected function qrSvg(string $url): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(220, 1), new SvgImageBackEnd));

        return $writer->writeString($url);
    }
}
