@extends('layouts.app')
@section('title', __('messages.2fa_security'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.2fa_security') }}</h1>
    @if($user->hasTwoFactorEnabled())
    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700"><i class="fas fa-check me-1"></i>{{ __('messages.2fa_active') }}</span>
    @else
    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500">{{ __('messages.2fa_inactive') }}</span>
    @endif
</div>

@if(session('recovery_codes'))
<div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">
    <p class="text-sm font-semibold text-amber-800">{{ __('messages.2fa_recovery_codes') }}</p>
    <p class="mt-1 text-xs text-amber-700">{{ __('messages.2fa_recovery_hint') }}</p>
    <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-2 font-mono text-sm">
        @foreach(session('recovery_codes') as $code)
        <span class="rounded-lg bg-white border border-amber-200 px-2 py-1 text-center">{{ $code }}</span>
        @endforeach
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        @if($user->hasTwoFactorEnabled())
            <h2 class="text-sm font-semibold text-gray-800 mb-3">{{ __('messages.2fa_disable') }}</h2>
            <form action="{{ route('two-factor.disable') }}" method="POST" class="space-y-3">
                @csrf @method('DELETE')
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('auth.password') }}</label>
                    <input type="password" name="password" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('messages.2fa_code') }}</label>
                    <input name="code" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm" placeholder="123456">
                </div>
                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700">{{ __('messages.2fa_disable') }}</button>
            </form>

            <hr class="my-5 border-gray-100">

            <h2 class="text-sm font-semibold text-gray-800 mb-3">{{ __('messages.2fa_regenerate') }}</h2>
            <form action="{{ route('two-factor.recovery') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('auth.password') }}</label>
                    <input type="password" name="password" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="rounded-xl bg-gray-800 px-4 py-2 text-sm text-white hover:bg-gray-900">{{ __('messages.2fa_regenerate') }}</button>
            </form>
        @elseif($pendingSecret)
            <h2 class="text-sm font-semibold text-gray-800 mb-3">{{ __('messages.2fa_scan_qr') }}</h2>
            <div class="flex justify-center rounded-xl border border-gray-100 bg-white p-4">{!! $qrSvg !!}</div>
            <div class="mt-4">
                <p class="text-xs text-gray-500">{{ __('messages.2fa_secret_key') }}</p>
                <p class="mt-1 break-all rounded-lg bg-gray-50 px-3 py-2 font-mono text-sm">{{ $pendingSecret }}</p>
            </div>
            <form action="{{ route('two-factor.confirm') }}" method="POST" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('messages.2fa_code') }}</label>
                    <input name="code" inputmode="numeric" autocomplete="one-time-code" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm" placeholder="123456">
                </div>
                <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">{{ __('messages.2fa_confirm') }}</button>
            </form>
        @else
            <h2 class="text-sm font-semibold text-gray-800 mb-3">{{ __('messages.2fa_enable') }}</h2>
            <p class="text-sm text-gray-500 mb-4">{{ __('messages.2fa_challenge_hint') }}</p>
            <form action="{{ route('two-factor.enable') }}" method="POST">
                @csrf
                <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                    <i class="fas fa-shield-halved me-2"></i>{{ __('messages.2fa_enable') }}
                </button>
            </form>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-sm text-gray-600 leading-7">
        <h2 class="text-sm font-semibold text-gray-800 mb-3">{{ __('messages.2fa_recovery_codes') }}</h2>
        <p>{{ __('messages.2fa_recovery_hint') }}</p>
        @if($user->hasTwoFactorEnabled())
        <p class="mt-3 text-xs text-gray-400">{{ count($user->recoveryCodes()) }} / 8</p>
        @endif
    </div>
</div>
@endsection
