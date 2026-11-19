<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4F46E5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
    <link rel="manifest" href="{{ url('/manifest.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/ip-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icons/ip-16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/ip-180.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/ip-192.png') }}">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('{{ url('/sw.js?v=15') }}', { scope: '/' }).catch(function () {});
            });
        }
    </script>

    <title>{{ config('app.name', 'Air Menu') }} - @yield('title', 'Client Portal')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            try {
                if (localStorage.getItem('ip_theme') === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/dark.css?v=4') }}">

    <style>
        .portal-sidebar-body .sidebar-link { color: #374151 !important; }
        .portal-sidebar-body .sidebar-link:hover { background-color: #eff6ff !important; color: #1d4ed8 !important; }
        .portal-sidebar-body .sidebar-link.active { background-color: #dbeafe !important; color: #1d4ed8 !important; font-weight: 500 !important; }
    </style>
</head>
<body class="antialiased bg-gray-50 text-gray-900 portal-sidebar-body">
    <div class="min-h-screen bg-gray-50 flex flex-col">
        @auth('client')
            @include('portal.partials.sidebar')

            <div class="{{ is_rtl() ? 'mr-64' : 'ml-64' }} min-h-screen transition-all duration-300 flex-1 flex flex-col">
                <header class="sticky top-0 z-30 bg-white border-b border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between h-16 px-6">
                        <div class="flex items-center gap-3">
                            <button onclick="var s=document.getElementById('portal-sidebar');s.classList.toggle('translate-x-full');s.classList.toggle('-translate-x-full')" class="lg:hidden text-gray-500 hover:text-gray-700">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                            <nav class="hidden md:flex" aria-label="Breadcrumb">
                                <ol class="inline-flex items-center gap-2">
                                    <li class="text-gray-500">{{ __('portal.client_portal') }}</li>
                                    @hasSection('breadcrumb')
                                        @yield('breadcrumb')
                                    @endif
                                </ol>
                            </nav>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1">
                                <a href="{{ route('lang.switch', 'ar') }}"
                                   class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition {{ app()->getLocale() === 'ar' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                                    <img src="https://flagcdn.com/24x18/sa.png" alt="عربي" class="w-4 h-3 rounded-sm object-cover">
                                </a>
                                <a href="{{ route('lang.switch', 'en') }}"
                                   class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition {{ app()->getLocale() === 'en' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                                    <img src="https://flagcdn.com/24x18/gb.png" alt="English" class="w-4 h-3 rounded-sm object-cover">
                                </a>
                            </div>

                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="flex items-center gap-2 text-gray-700 hover:text-gray-900 transition">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-primary-700 font-bold text-sm">
                                        {{ mb_substr(auth('client')->user()->client_name ?? '?', 0, 2) }}
                                    </div>
                                    <span class="text-sm font-medium hidden md:block">{{ auth('client')->user()->client_name }}</span>
                                    <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                                </button>

                                <div x-show="open"
                                     @click.away="open = false"
                                     x-transition
                                     class="absolute {{ is_rtl() ? 'left-0' : 'right-0' }} mt-2 w-52 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                    <a href="{{ route('portal.profile') }}"
                                       class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 transition">
                                        <i class="fas fa-user w-4 text-center text-gray-400"></i>
                                        <span>{{ __('portal.my_account') }}</span>
                                    </a>
                                    <button type="button" id="portalEnablePush" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 transition text-start">
                                        <i class="fas fa-bell w-4 text-center text-gray-400"></i>
                                        <span>{{ __('messages.push_enable') }}</span>
                                    </button>
                                    <p id="portalPushStatus" class="px-4 pb-1 text-xs text-gray-400"></p>
                                    <div class="border-t border-gray-100 my-1.5"></div>
                                    <form method="POST" action="{{ route('portal.logout') }}">
                                        @csrf
                                        <button type="submit"
                                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                            <i class="fas fa-sign-out-alt w-4 text-center"></i>
                                            <span>{{ __('portal.logout') }}</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="p-6 flex-1">
                    @if(session('success'))
                        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check-circle text-green-600"></i>
                                <span>{{ session('success') }}</span>
                                <button @click="$el.parentElement.parentElement.remove()" class="me-1 text-green-600 hover:text-green-800 me-auto">&times;</button>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-exclamation-circle text-red-600"></i>
                                <span>{{ session('error') }}</span>
                                <button @click="$el.parentElement.parentElement.remove()" class="me-1 text-red-600 hover:text-red-800 me-auto">&times;</button>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @yield('content')
                </main>

                <footer class="mt-auto bg-white border-t border-gray-200 py-4 px-6">
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <p>
                            &copy; {{ date('Y') }} <a href="https://airmenu.net" target="_blank" class="font-semibold text-primary-600 hover:underline">Air Menu</a>.
                            {{ __('common.all_rights_reserved') }}
                        </p>
                        <p>
                            <span class="text-primary-600 font-semibold">{{ config('app.name') }}</span> v1.0.0
                        </p>
                    </div>
                </footer>
            </div>
        @else
            <main class="flex-1">
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    @if(session('success'))
                        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check-circle text-green-600"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-exclamation-circle text-red-600"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    @yield('content')
                </div>
            </main>
        @endauth
    </div>
    <script>
        // Remember last visited page for the offline screen
        (function () {
            try {
                if (navigator.onLine) {
                    localStorage.setItem('ip_last_path', location.pathname + location.search);
                    localStorage.setItem('ip_last_at', String(Date.now()));
                }
            } catch (e) {}
        })();
    </script>

    <script>
        (function () {
            const btn = document.getElementById('portalEnablePush');
            if (!btn) return;
            if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
                btn.style.display = 'none';
                return;
            }
            const status = document.getElementById('portalPushStatus');
            const keyUrl = @json(route('portal.push.key'));
            const subUrl = @json(route('portal.push.subscribe'));
            const unsubUrl = @json(route('portal.push.unsubscribe'));
            const csrf = @json(csrf_token());
            const say = (t) => { if (status) status.textContent = t; };
            const b64 = (s) => {
                const pad = '='.repeat((4 - s.length % 4) % 4);
                const raw = atob((s + pad).replace(/-/g, '+').replace(/_/g, '/'));
                return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
            };
            const ready = () => navigator.serviceWorker.ready;
            async function subscription() {
                const reg = await ready();
                return reg.pushManager.getSubscription();
            }
            async function refresh() {
                const sub = await subscription();
                say(sub ? @json(__('messages.push_enabled')) : '');
            }
            btn.addEventListener('click', async function () {
                try {
                    btn.disabled = true;
                    const current = await subscription();
                    if (current) {
                        await fetch(unsubUrl, {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            body: JSON.stringify({ endpoint: current.endpoint })
                        });
                        await current.unsubscribe();
                        say(@json(__('messages.push_disabled')));
                        btn.disabled = false;
                        return;
                    }
                    const perm = await Notification.requestPermission();
                    if (perm !== 'granted') { say(@json(__('messages.push_denied'))); btn.disabled = false; return; }
                    const res = await fetch(keyUrl, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) { say(@json(__('messages.push_unconfigured'))); btn.disabled = false; return; }
                    const { publicKey } = await res.json();
                    const reg = await ready();
                    const sub = await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: b64(publicKey) });
                    await fetch(subUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify(sub)
                    });
                    say(@json(__('messages.push_enabled')));
                } catch (e) {
                    say(@json(__('messages.push_unavailable')));
                } finally {
                    btn.disabled = false;
                }
            });
            refresh();
        })();
    </script>

    <script>
        (function () {
            const buttons = Array.from(document.querySelectorAll('#themeToggle,[data-theme-toggle]'));
            if (!buttons.length) return;
            function paint() {
                const dark = document.documentElement.classList.contains('dark');
                buttons.forEach(function (btn) {
                    const label = btn.querySelector('[data-theme-label]');
                    const icon = btn.querySelector('i');
                    if (label) label.textContent = dark ? @json(__('messages.theme_light')) : @json(__('messages.theme_dark'));
                    if (icon) icon.className = dark ? 'fas fa-sun w-4 text-center' : 'fas fa-moon w-4 text-center';
                });
            }
            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const dark = document.documentElement.classList.toggle('dark');
                    try { localStorage.setItem('ip_theme', dark ? 'dark' : 'light'); } catch (e) {}
                    paint();
                });
            });
            paint();
        })();
    </script>
</body>
</html>