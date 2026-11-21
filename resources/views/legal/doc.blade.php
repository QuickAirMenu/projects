@php
    $doc = __('legal.'.$docKey);
    $companyEmail = \App\Models\Setting::query()->where('key', 'company_email')->value('value') ?: 'support@i-projects.net';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4F46E5">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/ip-32.png') }}">
    <title>{{ $doc['title'] }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    @include('partials.theme-head')
</head>
<body class="bg-gray-50">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:underline">
            <i class="fas fa-arrow-{{ is_rtl() ? 'right' : 'left' }}"></i>{{ __('legal.back_home') }}
        </a>

        <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 sm:p-8 shadow-sm">
            <h1 class="text-2xl font-bold text-gray-800">{{ $doc['title'] }}</h1>
            <p class="mt-1 text-xs text-gray-400">{{ __('legal.updated') }}</p>
            <p class="mt-4 text-sm leading-7 text-gray-600">{{ $doc['intro'] }}</p>

            <div class="mt-6 space-y-5">
                @foreach($doc['sections'] as $section)
                <section>
                    <h2 class="text-base font-semibold text-gray-800">{{ $section['heading'] }}</h2>
                    <p class="mt-1 text-sm leading-7 text-gray-600">{{ str_replace(':email', $companyEmail, $section['body']) }}</p>
                </section>
                @endforeach
            </div>
        </div>
    </div>
    @include('partials.theme-toggle')
</body>
</html>
