<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <script id="ip_privacy_boot">try{if(localStorage.getItem('privacyMode')==='on'){document.documentElement.classList.add('privacy-on');}}catch(e){}</script>
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


    <title>{{ config('app.name', 'Air Menu') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Styles -->
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
    <link rel="stylesheet" href="{{ asset('css/dark.css?v=5') }}">

    
    @stack('styles')
</head>
<body class="antialiased"
      x-data="{ pageLoading: false }"
      x-on:click.capture="
          const t = $event.target;
          if (t && t.closest && t.closest('a') && !t.closest('a').target && !t.closest('a').hasAttribute('data-no-loader') && t.closest('a').href && !t.closest('a').href.startsWith('#') && !t.closest('a').href.startsWith('javascript:')) {
              pageLoading = true;
          }
      "
      x-on:pageshow.window="pageLoading = false">
    {{-- Top progress bar shown during page navigation --}}
    <div x-show="pageLoading" x-cloak
         class="fixed top-0 left-0 right-0 h-1 z-[9999] overflow-hidden bg-transparent pointer-events-none">
        <div class="h-full w-1/3 bg-gradient-to-r from-blue-500 via-indigo-500 to-blue-500 animate-[pageLoader_1.2s_ease-in-out_infinite]"></div>
    </div>

    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Main Content Area -->
        <div class="{{ is_rtl() ? 'mr-64' : 'ml-64' }} min-h-screen transition-all duration-300 flex-1 flex flex-col">
            <!-- Header -->
            @include('layouts.partials.header')

                <!-- Page Content -->
                <main class="p-6 flex-1">
                    <!-- Page Header -->
                    @hasSection('page-header')
                        <div class="mb-6">
                            @yield('page-header')
                        </div>
                    @endif

                    <!-- Main Content -->
                    @yield('content')
                </main>

            <!-- Footer -->
            @include('layouts.partials.footer')
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-data="{ open: false }"
         x-on:open-delete-modal.window="open = true"
         x-show="open"
         x-cloak
         role="alertdialog"
         aria-modal="true"
         aria-labelledby="delete-modal-title"
         aria-describedby="delete-modal-desc"
         class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative z-10 bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 p-6 text-center">
            <div class="text-red-500 text-5xl mb-4"><i class="fas fa-exclamation-triangle"></i></div>
            <h3 id="delete-modal-title" class="text-lg font-bold text-gray-800 mb-2">{{ __('messages.delete_confirm_title') }}</h3>
            <p id="delete-modal-desc" class="text-gray-500 mb-6">{{ __('messages.delete_confirm_message') }}</p>
            <div class="flex gap-3 justify-center">
                <button @click="open = false" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium">{{ __('messages.cancel') }}</button>
                <button @click="open = false; if(window._deleteForm) { window._deleteForm.submit(); window._deleteForm = null; }" class="px-6 py-2.5 bg-red-600 text-white rounded-xl font-medium">{{ __('messages.delete') }}</button>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input, imgId) {
            const file = input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                const el = document.getElementById(imgId);
                if (el.tagName === 'IMG') {
                    el.src = e.target.result;
                } else {
                    el.innerHTML = '<img src="' + e.target.result + '" class="h-full w-auto object-contain">';
                }
            }
            reader.readAsDataURL(file);
        }
    </script>

    <x-toast-container />

    {{-- Inline validation errors (only when no toast was flashed) --}}
    @if(isset($errors) && $errors->any() && !session('success') && !session('error') && !session('warning') && !session('info'))
        <div x-data="{ show: true }" x-show="show" x-transition
             class="fixed top-4 end-4 z-[9999] w-full max-w-sm rounded-xl border border-red-200 bg-red-50 text-red-800 shadow-lg p-4"
             role="alert">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-red-500 text-lg mt-0.5"></i>
                <div class="flex-1">
                    <div class="font-semibold text-sm mb-1">{{ __('validation.failed') }}</div>
                    <ul class="text-sm list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button @click="show = false" class="opacity-60 hover:opacity-100"><i class="fas fa-times"></i></button>
            </div>
        </div>
    @endif

    @stack('scripts')
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
        // Browser push notifications
        (function () {
            const btn = document.getElementById('enablePushBtn');
            if (!btn) return;
            if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
                btn.style.display = 'none';
                return;
            }
            const status = document.getElementById('pushStatus');
            const keyUrl = @json(route('push.key'));
            const subUrl = @json(route('push.subscribe'));
            const unsubUrl = @json(route('push.unsubscribe'));
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
                btn.dataset.on = sub ? '1' : '0';
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
                        btn.dataset.on = '0';
                        btn.disabled = false;
                        return;
                    }

                    const perm = await Notification.requestPermission();
                    if (perm !== 'granted') {
                        say(@json(__('messages.push_denied')));
                        btn.disabled = false;
                        return;
                    }

                    const res = await fetch(keyUrl, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) {
                        say(@json(__('messages.push_unconfigured')));
                        btn.disabled = false;
                        return;
                    }

                    const { publicKey } = await res.json();
                    const reg = await ready();
                    const sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: b64(publicKey)
                    });

                    await fetch(subUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify(sub)
                    });

                    say(@json(__('messages.push_enabled')));
                    btn.dataset.on = '1';
                } catch (e) {
                    say(@json(__('messages.push_unavailable')));
                } finally {
                    btn.disabled = false;
                }
            });

            refresh();
        })();
    </script>
    <script src="{{ asset('js/searchable-select.js?v=3') }}" defer></script>

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
<script type="module">
    (function () {
        function autoMask() {
            var skip = 'script,style,input,select,textarea,option,canvas,svg,pre,code,button';
            var re = /^(?=.*[\d\u0660-\u0669])[\d\u0660-\u0669\s.,:%+\-—–()\/]*(\s?(SAR|EGP|USD|AED|ر\.س|ج\.م|ريال|جنيه|درهم))?$/u;

            document.querySelectorAll('td, th, p, span, div, a, strong, li, h1, h2, h3, h4, dd, dt').forEach(function (el) {
                if (el.children.length > 0 || el.closest(skip)) return;
                var text = (el.textContent || '').trim();
                if (text.length < 2 || text.length > 60) return;
                if (re.test(text)) el.classList.add('privacy-mask-js');
            });
        }

        window.togglePrivacy = function () {
            var on = !document.documentElement.classList.contains('privacy-on');
            document.documentElement.classList.toggle('privacy-on', on);
            document.body.classList.toggle('privacy-on', on);
            try { localStorage.setItem('privacyMode', on ? 'on' : 'off'); } catch (e) {}
            var icon = document.getElementById('privacyIcon');
            if (icon) icon.className = on ? 'fas fa-eye-slash w-4 text-center text-amber-500' : 'fas fa-eye w-4 text-center text-gray-400';
            if (on) autoMask();
        };

        function boot() {
            var on = false;
            try { on = localStorage.getItem('privacyMode') === 'on'; } catch (e) {}
            if (on) {
                document.documentElement.classList.add('privacy-on');
                document.body.classList.add('privacy-on');
                autoMask();
                var icon = document.getElementById('privacyIcon');
                if (icon) icon.className = 'fas fa-eye-slash w-4 text-center text-amber-500';
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }
    })();
</script>
<script type="module">
    (function () {
        var map = {'٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9','۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9','٫':'.','٬':'','，':','};
        function latinize(v) { return v.replace(/[٠-٩۰-۹٫٬，]/g, function (c) { return map[c] || c; }); }
        function prep(input) {
            if (!input || input.dataset.latinReady === '1') return;
            if (input.type === 'number') { input.type = 'text'; input.inputMode = 'decimal'; input.dataset.latinReady = '1'; }
        }
        function prepAll(root) { (root || document).querySelectorAll('input[type="number"]').forEach(prep); }
        document.addEventListener('DOMContentLoaded', function () { prepAll(); });
        document.addEventListener('input', function (e) {
            var el = e.target;
            if (!el || el.tagName !== 'INPUT' || el.dataset.latinReady !== '1') return;
            var converted = latinize(el.value);
            if (converted !== el.value) el.value = converted;
        });
        document.addEventListener('submit', function (e) {
            (e.target.querySelectorAll ? e.target : document).querySelectorAll('input[data-latin-ready="1"]').forEach(function (el) {
                el.value = latinize(el.value);
            });
        }, true);
        var observer = new MutationObserver(function (muts) { muts.forEach(function (m) { m.addedNodes.forEach(function (n) { if (n.nodeType === 1) prepAll(n); }); }); });
        document.addEventListener('DOMContentLoaded', function () { observer.observe(document.body, { childList: true, subtree: true }); });
    })();
</script>
</body>
</html>
