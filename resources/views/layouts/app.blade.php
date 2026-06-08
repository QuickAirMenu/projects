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
                @include('layouts.components.alert')

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
         class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative z-10 bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 p-6 text-center">
            <div class="text-red-500 text-5xl mb-4"><i class="fas fa-exclamation-triangle"></i></div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">تأكيد الحذف</h3>
            <p class="text-gray-500 mb-6">هل أنت متأكد من حذف هذا العنصر؟ لا يمكن التراجع عن هذا الإجراء.</p>
            <div class="flex gap-3 justify-center">
                <button @click="open = false" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium">إلغاء</button>
                <button @click="open = false; if(window._deleteForm) { window._deleteForm.submit(); window._deleteForm = null; }" class="px-6 py-2.5 bg-red-600 text-white rounded-xl font-medium">حذف</button>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
