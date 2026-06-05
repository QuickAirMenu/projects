<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Air Menu') }} - @yield('title', 'Client Portal')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 antialiased">
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-white/10 bg-slate-950/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ auth('client')->check() ? route('portal.dashboard') : route('portal.login') }}" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-950 font-bold">AM</div>
                    <div>
                        <div class="text-sm text-slate-400">Air Menu</div>
                        <div class="text-base font-semibold">Client Portal</div>
                    </div>
                </a>

                <div class="flex items-center gap-3 text-sm">
                    @auth('client')
                        <span class="hidden sm:inline text-slate-300">{{ auth('client')->user()->client_name }}</span>
                        <form method="POST" action="{{ route('portal.logout') }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-white/10 px-4 py-2 text-slate-200 hover:bg-white/5">
                                Logout
                            </button>
                        </form>
                    @else
                        <span class="rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-emerald-200">Portal</span>
                    @endauth
                </div>
            </div>
        </header>

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-emerald-100">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 rounded-xl border border-red-400/20 bg-red-400/10 px-4 py-3 text-red-100">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 rounded-xl border border-red-400/20 bg-red-400/10 px-4 py-3 text-red-100">
                        {{ $errors->first() }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        <footer class="border-t border-white/10 py-4 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} Air Menu
        </footer>
    </div>
</body>
</html>
