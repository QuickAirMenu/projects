<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ __('messages.platform_tagline') }}">
    <title>{{ config('app.name') }} - {{ __('messages.welcome') }}</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-white antialiased">
    {{-- Navigation Header --}}
    <header class="sticky top-0 z-40 bg-slate-950/80 backdrop-blur border-b border-white/10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5 group">
                <span class="h-9 w-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center font-bold text-sm shadow-lg shadow-blue-500/30 group-hover:scale-105 transition">AM</span>
                <span class="font-bold text-lg tracking-tight">{{ config('app.name') }}</span>
            </a>

            <nav class="flex items-center gap-2 sm:gap-3">
                {{-- Language switcher --}}
                <div class="flex items-center bg-white/5 border border-white/10 rounded-xl overflow-hidden text-xs font-semibold">
                    <a href="{{ route('lang.switch', ['locale' => 'ar']) }}"
                       class="px-2.5 sm:px-3 py-1.5 transition {{ is_rtl() ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-white/10' }}"
                       aria-label="{{ __('messages.arabic') }}">{{ __('messages.lang_short_ar') }}</a>
                    <a href="{{ route('lang.switch', ['locale' => 'en']) }}"
                       class="px-2.5 sm:px-3 py-1.5 transition {{ !is_rtl() ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-white/10' }}"
                       aria-label="{{ __('messages.english') }}">{{ __('messages.lang_short_en') }}</a>
                </div>

                <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-semibold text-sm transition shadow-lg shadow-blue-500/20">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>{{ __('messages.login') }}</span>
                </a>
                <a href="{{ route('login') }}" class="sm:hidden inline-flex items-center justify-center h-9 w-9 rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition" aria-label="{{ __('messages.login') }}">
                    <i class="fas fa-sign-in-alt"></i>
                </a>
            </nav>
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10">
            <div class="absolute -top-24 -end-24 h-72 w-72 rounded-full bg-blue-600/30 blur-3xl"></div>
            <div class="absolute -bottom-24 -start-24 h-72 w-72 rounded-full bg-indigo-600/20 blur-3xl"></div>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 pt-16 sm:pt-24 pb-16 text-center">
            <div class="inline-flex items-center gap-2 bg-white/5 border border-white/10 rounded-full px-4 py-1.5 text-xs text-slate-300 mb-6">
                <span class="h-1.5 w-1.5 rounded-full bg-green-400 animate-pulse"></span>
                <span>{{ __('messages.welcome') }}</span>
            </div>

            <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight tracking-tight mb-5">
                {{ config('app.name') }}
            </h1>

            <p class="text-base sm:text-lg md:text-xl text-slate-300 max-w-2xl mx-auto leading-relaxed mb-10">
                {{ __('messages.platform_tagline') }}
            </p>

            <div class="flex flex-wrap gap-3 justify-center">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-7 py-3.5 rounded-xl font-semibold text-base sm:text-lg transition shadow-xl shadow-blue-500/30">
                    <i class="fas fa-rocket"></i>
                    <span>{{ __('messages.get_started') }}</span>
                </a>
                <a href="#features" class="inline-flex items-center gap-2 border border-white/20 hover:bg-white/10 text-white px-7 py-3.5 rounded-xl font-semibold text-base sm:text-lg transition">
                    <i class="fas fa-arrow-down"></i>
                    <span>{{ __('messages.explore_features') }}</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="py-16 sm:py-20 border-t border-white/5">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-10 sm:mb-12">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-3">{{ __('messages.explore_features') }}</h2>
                <p class="text-slate-400 max-w-2xl mx-auto">{{ __('messages.platform_tagline') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @php
                    $features = [
                        ['icon' => 'fa-briefcase',         'title' => __('messages.feature_projects_title'), 'desc' => __('messages.feature_projects_desc'),  'color' => 'blue'],
                        ['icon' => 'fa-tasks',             'title' => __('messages.feature_tasks_title'),    'desc' => __('messages.feature_tasks_desc'),     'color' => 'indigo'],
                        ['icon' => 'fa-file-invoice',      'title' => __('messages.feature_finance_title'),  'desc' => __('messages.feature_finance_desc'),   'color' => 'emerald'],
                        ['icon' => 'fa-users',             'title' => __('messages.feature_portal_title'),   'desc' => __('messages.feature_portal_desc'),    'color' => 'purple'],
                        ['icon' => 'fa-chart-bar',         'title' => __('messages.feature_reports_title'),  'desc' => __('messages.feature_reports_desc'),   'color' => 'amber'],
                        ['icon' => 'fa-shield-halved',     'title' => __('messages.feature_security_title'), 'desc' => __('messages.feature_security_desc'),  'color' => 'rose'],
                    ];
                @endphp
                @foreach($features as $f)
                    <div class="group bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-2xl p-6 transition">
                        <div class="h-12 w-12 rounded-xl bg-{{ $f['color'] }}-500/15 border border-{{ $f['color'] }}-500/20 flex items-center justify-center mb-5 group-hover:scale-110 transition">
                            <i class="fas {{ $f['icon'] }} text-{{ $f['color'] }}-400 text-xl"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2 text-white">{{ $f['title'] }}</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">{{ $f['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-16 sm:py-20 border-t border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 to-indigo-700 p-8 sm:p-12 text-center shadow-2xl shadow-blue-500/20">
                <div class="absolute -top-12 -end-12 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
                <div class="absolute -bottom-12 -start-12 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>

                <div class="relative">
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold mb-3">{{ __('messages.cta_title') }}</h2>
                    <p class="text-blue-100 max-w-xl mx-auto mb-8 text-sm sm:text-base">{{ __('messages.cta_subtitle') }}</p>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-white hover:bg-blue-50 text-blue-700 px-7 py-3.5 rounded-xl font-bold text-base sm:text-lg transition shadow-xl">
                        <i class="fas fa-rocket"></i>
                        <span>{{ __('messages.cta_button') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-white/10 py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-wrap items-center justify-between gap-3 text-slate-500 text-sm">
            <div>
                &copy; {{ date('Y') }} {{ config('app.name') }}.
                <span class="hidden sm:inline">{{ __('messages.copyright') }}.</span>
            </div>
            <div class="flex items-center gap-5">
                <a href="{{ route('login') }}" class="hover:text-white transition">{{ __('messages.login') }}</a>
                <a href="#features" class="hover:text-white transition">{{ __('messages.explore_features') }}</a>
            </div>
        </div>
    </footer>
</body>
</html>
