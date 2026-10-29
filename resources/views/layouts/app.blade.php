<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Air Menu') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
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
</body>
</html>
