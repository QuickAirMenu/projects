<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 {{ is_rtl() ? 'right-0' : 'left-0' }} z-40 w-64 h-screen transition-transform bg-white border-{{ is_rtl() ? 'l' : 'r' }} border-gray-200 custom-scrollbar overflow-y-auto flex flex-col">
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 border-b border-gray-200 bg-gradient-to-r from-primary-600 to-primary-700 flex-shrink-0">
        <h1 class="text-xl font-bold text-white">{{ config('app.name') }}</h1>
    </div>

    <!-- Navigation -->
    <nav class="p-3 space-y-1 flex-1 overflow-y-auto">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
            <span>{{ __('common.dashboard') }}</span>
        </a>

        @can('view-clients')
        <a href="{{ route('clients.index') }}" class="sidebar-link {{ request()->routeIs('clients.*') && !request()->routeIs('clients.communications.*') ? 'active' : '' }}">
            <i class="fas fa-users w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
            <span>{{ __('common.clients') }}</span>
        </a>
        @endcan

        @can('view-projects')
        <!-- Projects Group -->
        <div x-data="{ open: {{ request()->routeIs('projects.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-link w-full {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                <i class="fas fa-briefcase w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('common.projects') }}</span>
                <i class="fas fa-chevron-down text-xs transition-transform duration-200 {{ is_rtl() ? 'mr-auto' : 'ml-auto' }}" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-collapse class="{{ is_rtl() ? 'mr-4' : 'ml-4' }} space-y-1 mt-1">
                <a href="{{ route('projects.index') }}" class="sidebar-link text-sm {{ request()->routeIs('projects.index') ? 'active' : '' }}">
                    <i class="fas fa-table w-5 text-center {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                    <span>{{ __('project.list_view') }}</span>
                </a>
                <a href="{{ route('projects.board') }}" class="sidebar-link text-sm {{ request()->routeIs('projects.board') ? 'active' : '' }}">
                    <i class="fas fa-columns w-5 text-center {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                    <span>{{ __('project.kanban') }}</span>
                </a>
                <a href="{{ route('projects.calendar') }}" class="sidebar-link text-sm {{ request()->routeIs('projects.calendar') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt w-5 text-center {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                    <span>{{ __('project.calendar') }}</span>
                </a>
                <a href="{{ route('projects.timeline') }}" class="sidebar-link text-sm {{ request()->routeIs('projects.timeline') ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-5 text-center {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                    <span>{{ __('project.timeline') }}</span>
                </a>
            </div>
        </div>
        @endcan

        @can('view-tasks')
        <!-- Tasks Group -->
        <div x-data="{ open: {{ request()->routeIs('tasks.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-link w-full {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                <i class="fas fa-tasks w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('common.tasks') }}</span>
                <i class="fas fa-chevron-down text-xs transition-transform duration-200 {{ is_rtl() ? 'mr-auto' : 'ml-auto' }}" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-collapse class="{{ is_rtl() ? 'mr-4' : 'ml-4' }} space-y-1 mt-1">
                <a href="{{ route('tasks.index') }}" class="sidebar-link text-sm {{ request()->routeIs('tasks.index') ? 'active' : '' }}">
                    <i class="fas fa-table w-5 text-center {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                    <span>{{ __('task.list_view') }}</span>
                </a>
                <a href="{{ route('tasks.kanban') }}" class="sidebar-link text-sm {{ request()->routeIs('tasks.kanban') ? 'active' : '' }}">
                    <i class="fas fa-columns w-5 text-center {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                    <span>{{ __('task.kanban') }}</span>
                </a>
                <a href="{{ route('tasks.calendar') }}" class="sidebar-link text-sm {{ request()->routeIs('tasks.calendar') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt w-5 text-center {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                    <span>{{ __('task.calendar') }}</span>
                </a>
                <a href="{{ route('tasks.timeline') }}" class="sidebar-link text-sm {{ request()->routeIs('tasks.timeline') ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-5 text-center {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                    <span>{{ __('task.timeline') }}</span>
                </a>
            </div>
        </div>
        @endcan

        <!-- Finance Section -->
        @canany(['view-quotes', 'view-invoices', 'view-payments', 'view-contracts'])
        <div x-data="{ open: {{ request()->routeIs('quotes.*') || request()->routeIs('invoices.*') || request()->routeIs('payments.*') || request()->routeIs('contracts.*') ? 'true' : 'false' }} }" class="pt-2">
            <button @click="open = !open" class="sidebar-link w-full {{ request()->routeIs('quotes.*') || request()->routeIs('invoices.*') || request()->routeIs('payments.*') || request()->routeIs('contracts.*') ? 'active' : '' }}">
                <i class="fas fa-coins w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('common.finance') }}</span>
                <i class="fas fa-chevron-down text-xs transition-transform duration-200 {{ is_rtl() ? 'mr-auto' : 'ml-auto' }}" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-collapse class="space-y-1">
                @can('view-quotes')
                <a href="{{ route('quotes.index') }}" class="sidebar-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                    <span>{{ __('common.quotes') }}</span>
                </a>
                @endcan
                @can('view-invoices')
                <a href="{{ route('invoices.index') }}" class="sidebar-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <i class="fas fa-receipt w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                    <span>{{ __('common.invoices') }}</span>
                </a>
                @endcan
                @can('view-payments')
                <a href="{{ route('payments.index') }}" class="sidebar-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                    <span>{{ __('common.payments') }}</span>
                </a>
                @endcan
                @can('view-contracts')
                <a href="{{ route('contracts.index') }}" class="sidebar-link {{ request()->routeIs('contracts.*') ? 'active' : '' }}">
                    <i class="fas fa-file-contract w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                    <span>{{ __('common.contracts') }}</span>
                </a>
                @endcan
            </div>
        </div>
        @endcanany

        <!-- System Section -->
        @canany(['view-reports', 'manage-settings', 'manage-users'])
        <div class="pt-2">
            <p class="px-4 mb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('common.system') }}</p>
            @can('view-reports')
            <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('common.reports') }}</span>
            </a>
            @endcan
            @can('manage-users')
            <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users-gear w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('common.users') }}</span>
            </a>
            @endcan
            @can('manage-settings')
            <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="fas fa-cog w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('common.settings') }}</span>
            </a>
            @endcan
        </div>
        @endcanany

        <!-- Export / Import -->
        <div class="pt-2">
            <p class="px-4 mb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">@lang('common.import_export')</p>
            <a href="{{ route('imports.index') }}" class="sidebar-link {{ request()->routeIs('imports.*') ? 'active' : '' }}">
                <i class="fas fa-upload w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>@lang('common.import_data')</span>
            </a>
        </div>

        <!-- Help + Support -->
        <div class="pt-2">
            <a href="{{ route('help') }}" class="sidebar-link {{ request()->routeIs('help') ? 'active' : '' }}">
                <i class="fas fa-circle-question w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('help.title') }}</span>
            </a>
            <a href="{{ route('support') }}" class="sidebar-link {{ request()->routeIs('support') ? 'active' : '' }}">
                <i class="fas fa-headset w-5 text-center {{ is_rtl() ? 'ml-3' : 'mr-3' }}"></i>
                <span>{{ __('support.title') }}</span>
            </a>
        </div>
    </nav>

    <!-- User Info -->
    <div class="p-4 border-t border-gray-200 bg-gray-50 flex-shrink-0">
        <div class="flex items-center">
            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-full {{ is_rtl() ? 'ml-3' : 'mr-3' }}">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>
</aside>
