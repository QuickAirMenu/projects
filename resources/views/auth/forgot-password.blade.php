<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4F46E5">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/ip-32.png') }}">
    <title>{{ __('auth.forgot_password') }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    @include('partials.theme-head')
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <div class="text-center mb-6">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-blue-600"><i class="fas fa-key text-xl"></i></div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('auth.forgot_password') }}</h1>
            <p class="mt-2 text-sm text-gray-500">{{ __('auth.forgot_password_hint') }}</p>
        </div>

        @if(session('status'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
            </div>
            <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">{{ __('auth.send_reset_link') }}</button>
        </form>

        <div class="mt-5 text-center">
            <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline"><i class="fas fa-arrow-{{ is_rtl() ? 'right' : 'left' }} me-1"></i>{{ __('auth.back_to_login') }}</a>
        </div>
    </div>
    @include('partials.theme-toggle')
</body>
</html>
