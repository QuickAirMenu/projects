<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserInvitationController extends Controller
{
    public function showSetup(Request $request, string $token)
    {
        $user = User::where('invitation_token', $token)
            ->where('invitation_expires_at', '>', now())
            ->firstOrFail();

        return view('auth.invitation-setup', compact('user', 'token'));
    }

    public function completeSetup(Request $request, string $token)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('invitation_token', $token)
            ->where('invitation_expires_at', '>', now())
            ->firstOrFail();

        $user->update([
            'password' => Hash::make($request->password),
            'invitation_token' => null,
            'invitation_expires_at' => null,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        return redirect()->route('login')
            ->with('success', 'تم إعداد الحساب بنجاح. يمكنك تسجيل الدخول الآن.');
    }
}
