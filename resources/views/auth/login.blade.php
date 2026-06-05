<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.login') }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gradient-to-br from-primary-600 to-primary-800">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">

            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-lg mb-4">
                    <i class="fas fa-rocket text-4xl text-primary-600"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Air Menu | Workspace</h1>
                <p class="text-primary-100">{{ __('auth.welcome_back') }}</p>
            </div>

            <!-- Login Form -->
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                    {{ __('auth.login') }}
                </h2>

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                            {{ __('auth.email') }}
                        </label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                            class="form-input @error('email') border-red-500 @enderror"
                            required autofocus placeholder="{{ __('auth.email_placeholder') }}">
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                            {{ __('auth.password') }}
                        </label>
                        <input id="password" type="password" name="password"
                            class="form-input @error('password') border-red-500 @enderror"
                            required placeholder="{{ __('auth.password_placeholder') }}">
                        @error('password')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="{{ is_rtl() ? 'mr-2' : 'ml-2' }} text-sm text-gray-700">{{ __('auth.remember_me') }}</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                        {{ __('auth.login') }}
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center">
                @if(app()->getLocale() == 'ar')
                    <a href="{{ route('lang.switch', 'en') }}"
                        style="display:inline-flex;align-items:center;gap:8px;padding:8px 20px;background:rgba(255,255,255,0.2);color:#fff;font-size:14px;font-weight:600;border-radius:8px;border:1px solid rgba(255,255,255,0.5);text-decoration:none;margin-bottom:12px;">
                        <i class="fas fa-language"></i> English
                    </a>
                @else
                    <a href="{{ route('lang.switch', 'ar') }}"
                        style="display:inline-flex;align-items:center;gap:8px;padding:8px 20px;background:rgba(255,255,255,0.2);color:#fff;font-size:14px;font-weight:600;border-radius:8px;border:1px solid rgba(255,255,255,0.5);text-decoration:none;margin-bottom:12px;">
                        <i class="fas fa-language"></i> عربي
                    </a>
                @endif
                <p class="text-sm text-primary-100">
                    &copy; {{ date('Y') }} <a href="https://airmenu.net" target="_blank" style="color:inherit;opacity:0.8;">Air Menu</a> {{ __('common.all_rights_reserved') }}
                </p>
            </div>

        </div>
    </div>
</body>
</html>
