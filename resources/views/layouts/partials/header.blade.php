<!-- Header -->
<header class="sticky top-0 z-30 bg-white border-b border-gray-200 shadow-sm">
    <div class="flex items-center justify-between h-16 px-6">
        <!-- Left Side -->
        <div class="flex items-center space-x-4 {{ is_rtl() ? 'space-x-reverse' : '' }}">
            <!-- Mobile Menu Toggle -->
            <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700">
                <i class="fas fa-bars text-xl"></i>
            </button>

            <!-- Breadcrumb -->
            <nav class="hidden md:flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-2 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-home"></i>
                        </a>
                    </li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>

        <!-- Right Side -->
        <div class="flex items-center space-x-4 {{ is_rtl() ? 'space-x-reverse' : '' }}">
            <!-- Language Switcher -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center text-gray-700 hover:text-gray-900">
                    <i class="fas fa-language {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                    <span class="text-sm">{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                    <i class="fas fa-chevron-down text-xs {{ is_rtl() ? 'mr-2' : 'ml-2' }}"></i>
                </button>

                <div x-show="open" 
                     @click.away="open = false"
                     x-transition
                     class="absolute {{ is_rtl() ? 'left-0' : 'right-0' }} mt-2 w-40 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                    <a href="{{ route('lang.switch', 'ar') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-check {{ app()->getLocale() === 'ar' ? '' : 'invisible' }} {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                        العربية
                    </a>
                    <a href="{{ route('lang.switch', 'en') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-check {{ app()->getLocale() === 'en' ? '' : 'invisible' }} {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                        English
                    </a>
                </div>
            </div>

            <!-- Notifications -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="relative p-2 text-gray-700 hover:text-gray-900">
                    <i class="fas fa-bell text-xl"></i>
                    <span class="absolute top-0 {{ is_rtl() ? 'left-0' : 'right-0' }} inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">3</span>
                </button>

                <div x-show="open" 
                     @click.away="open = false"
                     x-transition
                     class="absolute {{ is_rtl() ? 'left-0' : 'right-0' }} mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-900">{{ __('common.notifications') }}</h3>
                    </div>
                    <div class="max-h-96 overflow-y-auto custom-scrollbar">
                        <div class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer">
                            <p class="text-sm text-gray-900">{{ __('common.sample_notification') }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ __('common.time_ago', ['time' => '5 دقائق']) }}</p>
                        </div>
                    </div>
                    <div class="p-3 border-t border-gray-200 text-center">
                        <a href="#" class="text-sm text-primary-600 hover:text-primary-700">{{ __('common.view_all') }}</a>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center text-gray-700 hover:text-gray-900">
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full {{ is_rtl() ? 'ml-2' : 'mr-2' }}">
                    <span class="text-sm font-medium hidden md:block">{{ auth()->user()->name }}</span>
                    <i class="fas fa-chevron-down text-xs {{ is_rtl() ? 'mr-2' : 'ml-2' }}"></i>
                </button>

                <div x-show="open" 
                     @click.away="open = false"
                     x-transition
                     class="absolute {{ is_rtl() ? 'left-0' : 'right-0' }} mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                        {{ __('common.profile') }}
                    </a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-cog {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                        {{ __('common.settings') }}
                    </a>
                    <div class="border-t border-gray-200 my-2"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-{{ is_rtl() ? 'right' : 'left' }} px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            <i class="fas fa-sign-out-alt {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                            {{ __('common.logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
