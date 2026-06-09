<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 {{ is_rtl() ? 'right-0' : 'left-0' }} z-40 w-64 h-screen transition-transform bg-white border-{{ is_rtl() ? 'l' : 'r' }} border-gray-200 custom-scrollbar overflow-y-auto">
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 border-b border-gray-200 bg-gradient-to-r from-primary-600 to-primary-700">
        <h1 class="text-xl font-bold text-white">{{ config('app.name') }}</h1>
    </div>

    <!-- Navigation -->
    <nav class="p-4 space-y-2">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
            <span>{{ __('common.dashboard') }}</span>
        </a>

        @can('view-clients')
        <!-- Clients -->
        <a href="{{ route('clients.index') }}" class="sidebar-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
            <i class="fas fa-users {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
            <span>{{ __('common.clients') }}</span>
        </a>
        @endcan

        @can('view-projects')
        <!-- Projects -->
        <a href="{{ route('projects.index') }}" class="sidebar-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
            <i class="fas fa-briefcase {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
            <span>{{ __('common.projects') }}</span>
        </a>
        @endcan

        @can('view-tasks')
        <!-- Tasks -->
        <a href="{{ route('tasks.index') }}" class="sidebar-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
            <i class="fas fa-tasks {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
            <span>{{ __('common.tasks') }}</span>
        </a>
        @endcan

        <!-- Finance Section -->
        @canany(['view-quotes', 'view-invoices', 'view-payments'])
        <div class="pt-4 mt-4 border-t border-gray-200">
            <p class="px-4 mb-2 text-xs font-semibold text-gray-400 uppercase">{{ __('common.finance') }}</p>
            
            @can('view-quotes')
            <a href="{{ route('quotes.index') }}" class="sidebar-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('common.quotes') }}</span>
            </a>
            @endcan

            @can('view-invoices')
            <a href="{{ route('invoices.index') }}" class="sidebar-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                <i class="fas fa-receipt {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('common.invoices') }}</span>
            </a>
            @endcan

            @can('view-payments')
            <a href="{{ route('payments.index') }}" class="sidebar-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('common.payments') }}</span>
            </a>
            @endcan

            @can('view-contracts')
            <a href="{{ route('contracts.index') }}" class="sidebar-link {{ request()->routeIs('contracts.*') ? 'active' : '' }}">
                <i class="fas fa-file-contract {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('common.contracts') }}</span>
            </a>
            @endcan
        </div>
        @endcanany

        <!-- Reports & Settings -->
        @canany(['view-reports', 'manage-settings', 'manage-users'])
        <div class="pt-4 mt-4 border-t border-gray-200">
            <p class="px-4 mb-2 text-xs font-semibold text-gray-400 uppercase">{{ __('common.system') }}</p>
            
            @can('view-reports')
            <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('common.reports') }}</span>
            </a>
            @endcan

            @can('manage-users')
            <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-user-cog {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('common.users') }}</span>
            </a>
            @endcan

            @can('manage-settings')
            <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="fas fa-cog {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('common.settings') }}</span>
            </a>
            @endcan

            <a href="{{ route('help') }}" class="sidebar-link {{ request()->routeIs('help') ? 'active' : '' }}">
                <i class="fas fa-circle-question {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>مركز المساعدة</span>
            </a>
        </div>
        @endcanany
    </nav>

    <!-- Support -->
    <div class="px-4 py-3 border-t border-gray-200">
        <p class="text-xs font-semibold text-gray-400 uppercase mb-2">الدعم</p>
        <a href="https://wa.me/966500000000" target="_blank" class="flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 py-1">
            <i class="fab fa-whatsapp text-green-500"></i> WhatsApp
        </a>
        <a href="mailto:support@airmenu.net" class="flex items-center gap-2 text-sm text-gray-600 hover:text-blue-600 py-1">
            <i class="fas fa-envelope text-blue-500"></i> support@airmenu.net
        </a>
    </div>

    <!-- User Info at Bottom -->
    <div class="absolute bottom-0 w-full p-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center">
            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-full {{ is_rtl() ? 'ml-3' : 'mr-3' }}">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>
</aside>
