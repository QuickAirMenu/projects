<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Air Menu | Workspace') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="antialiased">
    <div class="min-h-screen bg-gray-50">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Main Content Area -->
        <div class="{{ is_rtl() ? 'mr-64' : 'ml-64' }} min-h-screen transition-all duration-300">
            <!-- Header -->
            @include('layouts.partials.header')

            <!-- Page Content -->
            <main class="p-6">
                <!-- Alerts -->
                @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative alert-auto-hide" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative alert-auto-hide" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

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

    @stack('scripts')
</body>
</html>
