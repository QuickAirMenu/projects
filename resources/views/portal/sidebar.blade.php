<aside id="portal-sidebar" class="fixed top-0 {{ is_rtl() ? 'right-0' : 'left-0' }} z-40 w-64 h-screen {{ is_rtl() ? 'translate-x-full' : '-translate-x-full' }} lg:translate-x-0 transition-transform bg-white border-{{ is_rtl() ? 'l' : 'r' }} border-gray-200 custom-scrollbar overflow-y-auto flex flex-col">
    <div class="flex items-center gap-3 px-5 h-16 border-b border-gray-200 bg-gradient-to-r from-primary-600 to-primary-700 flex-shrink-0">
        @php
            $sidebarLogo = workspace_config('company_logo');
            $sidebarName = app()->getLocale() === 'ar'
                ? (\App\Models\Setting::where('key', 'company_name_ar')->value('value') ?: config('app.name'))
                : (\App\Models\Setting::where('key', 'company_name')->value('value') ?: config('app.name'));
            $companyEmail = \App\Models\Setting::where('key', 'company_email')->value('value');
            $companyPhone = \App\Models\Setting::where('key', 'company_phone')->value('value');
            $companyAddress = \App\Models\Setting::where('key', 'company_address')->value('value');
        @endphp
        @if($sidebarLogo)
            <img src="{{ $sidebarLogo }}" alt="{{ $sidebarName }}" class="h-8 w-auto max-w-[140px] object-contain brightness-0 invert">
        @else
            <h1 class="text-xl font-bold text-white">{{ $sidebarName }}</h1>
        @endif
    </div>

    <nav class="p-3 space-y-1 flex-1 overflow-y-auto">
        <p class="px-4 mb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('common.main') }}</p>

        <a href="{{ route('portal.dashboard') }}" class="sidebar-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
            <span>{{ __('common.dashboard') }}</span>
        </a>

        <a href="{{ route('portal.dashboard') }}" class="sidebar-link {{ request()->routeIs('portal.projects.show') ? 'active' : '' }}">
            <i class="fas fa-briefcase w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
            <span>{{ __('common.projects') }}</span>
        </a>

        @if(portal_setting('portal_show_invoices') === '1')
        <a href="{{ route('portal.invoices') }}" class="sidebar-link {{ request()->routeIs('portal.invoices') ? 'active' : '' }}">
            <i class="fas fa-file-invoice w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
            <span>{{ __('finance.invoices') }}</span>
        </a>
        @endif

        @if(portal_setting('portal_show_quotes') === '1')
        <a href="{{ route('portal.quotes') }}" class="sidebar-link {{ request()->routeIs('portal.quotes') ? 'active' : '' }}">
            <i class="fas fa-file-alt w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
            <span>{{ __('finance.quotes') }}</span>
        </a>
        @endif

        <div class="pt-4 mt-4 border-t border-gray-100">
            <p class="px-4 mb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('common.account') }}</p>
            <a href="{{ route('portal.profile') }}" class="sidebar-link {{ request()->routeIs('portal.profile') ? 'active' : '' }}">
                <i class="fas fa-user w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('portal.my_account') }}</span>
            </a>
        </div>

        @if($companyEmail || $companyPhone || $companyAddress)
        <div class="pt-4 mt-4 border-t border-gray-100">
            <p class="px-4 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'معلومات الاتصال' : 'Contact' }}</p>
            <div class="px-4 space-y-2">
                @if($companyEmail)
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <i class="fas fa-envelope w-4 text-center text-gray-400"></i>
                    <span class="truncate">{{ $companyEmail }}</span>
                </div>
                @endif
                @if($companyPhone)
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <i class="fas fa-phone w-4 text-center text-gray-400"></i>
                    <span dir="ltr">{{ $companyPhone }}</span>
                </div>
                @endif
                @if($companyAddress)
                <div class="flex items-start gap-2 text-xs text-gray-500">
                    <i class="fas fa-location-dot w-4 text-center text-gray-400 mt-0.5"></i>
                    <span>{{ $companyAddress }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </nav>

    <div class="p-4 border-t border-gray-200 bg-gray-50 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 text-primary-700 font-bold text-sm shrink-0">
                {{ mb_substr(auth('client')->user()->client_name ?? '?', 0, 2) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ auth('client')->user()->client_name ?? '' }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth('client')->user()->client?->email ?? '' }}</p>
            </div>
        </div>
    </div>
</aside>