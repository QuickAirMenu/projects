<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/ip-32.png') }}">
    <title>{{ __('messages.2fa_challenge_title') }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme-head')
</head>
<body class="bg-gradient-to-br from-primary-700 to-primary-900">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-lg mb-4">
                    <i class="fas fa-shield-halved text-4xl text-primary-600"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">{{ __('messages.2fa_challenge_title') }}</h1>
                <p class="text-primary-100">{{ __('messages.2fa_challenge_hint') }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow-2xl p-8">
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('two-factor.login.store') }}">
                    @csrf
                    <div class="mb-6">
                        <label for="code" class="form-label">
                            <i class="fas fa-key {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                            {{ __('messages.2fa_code') }}
                        </label>
                        <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus required
                            class="form-input @error('code') border-red-500 @enderror" placeholder="123456">
                        @error('code')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="w-full btn btn-primary btn-lg">
                        <i class="fas fa-check {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                        {{ __('messages.2fa_verify') }}
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:underline">{{ __('auth.login') }}</a>
                </div>
            </div>
        </div>
    </div>
    @include('partials.theme-toggle')
</body>
</html>
