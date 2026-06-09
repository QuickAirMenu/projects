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
