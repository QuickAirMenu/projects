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
            <!-- Search Trigger (Cmd/Ctrl + K) -->
            <button type="button" @click="$dispatch('open-search')"
                    class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-xl text-sm transition">
                <i class="fas fa-search text-xs"></i>
                <span class="hidden md:inline">{{ __('common.search') }}…</span>
                <kbd class="hidden md:inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-semibold text-gray-500 bg-white border border-gray-200 rounded">
                    {{ is_rtl() ? 'Ctrl' : '⌘' }}K
                </kbd>
            </button>

            <!-- Privacy Toggle -->
            <button onclick="togglePrivacy()" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition" title="{{ __('common.privacy_mode') }}">
                <i id="privacyIcon" class="fas fa-eye text-lg"></i>
            </button>

            <!-- Language Switcher -->
            <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1">
                <a href="{{ route('lang.switch', 'ar') }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition {{ app()->getLocale() === 'ar' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                    <img src="https://flagcdn.com/24x18/sa.png" alt="عربي" class="w-4 h-3 rounded-sm object-cover">
                </a>
                <a href="{{ route('lang.switch', 'en') }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition {{ app()->getLocale() === 'en' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                    <img src="https://flagcdn.com/24x18/gb.png" alt="English" class="w-4 h-3 rounded-sm object-cover">
                </a>
            </div>

            <!-- Notifications Bell -->
            <a href="{{ route('notifications.index') }}"
               id="notifications-bell"
               class="relative p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition"
               title="{{ __('messages.notifications') }}">
                <i class="fas fa-bell text-lg"></i>
                <span id="notifications-badge"
                      class="absolute top-0 {{ is_rtl() ? 'left-0' : 'right-0' }} bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 {{ auth()->user()->unreadNotifications()->count() > 0 ? '' : 'hidden' }}">
                    {{ auth()->user()->unreadNotifications()->count() > 9 ? '9+' : auth()->user()->unreadNotifications()->count() }}
                </span>
            </a>

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
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                        {{ __('common.profile') }}
                    </a>
                    <a href="{{ route('settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
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

<!-- Search Palette (Cmd/Ctrl + K) -->
<div x-data="searchPalette()" x-init="init()"
     @keydown.window.prevent.cmd-k.window="open = true"
     @keydown.window.prevent.ctrl-k.window="open = true"
     @keydown.escape.window="open = false"
     @open-search.window="open = true"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-start justify-center pt-20 px-4">
    <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
    <div class="relative z-10 w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden"
         @click.away="open = false">
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100">
            <i class="fas fa-search text-gray-400"></i>
            <input type="text"
                   x-ref="searchInput"
                   x-model="q"
                   @input.debounce.250ms="search()"
                   placeholder="{{ __('common.search_placeholder') }}"
                   class="flex-1 bg-transparent border-0 focus:outline-none text-sm text-gray-800 placeholder-gray-400">
            <kbd class="px-1.5 py-0.5 text-[10px] font-semibold text-gray-500 bg-gray-100 border border-gray-200 rounded">ESC</kbd>
        </div>
        <div class="max-h-96 overflow-y-auto" x-show="q.length >= 2">
            <template x-if="loading">
                <div class="p-8 text-center text-gray-400 text-sm">
                    <i class="fas fa-spinner fa-spin me-2"></i>{{ __('common.searching') }}
                </div>
            </template>
            <template x-if="!loading && totalCount() === 0">
                <div class="p-8 text-center text-gray-400 text-sm">
                    <i class="fas fa-search text-2xl mb-2 block text-gray-200"></i>
                    {{ __('common.no_results') }}
                </div>
            </template>
            <template x-for="group in groups" :key="group.key">
                <div x-show="group.items.length > 0">
                    <div class="px-4 py-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50 border-b border-gray-100" x-text="group.label"></div>
                    <template x-for="item in group.items" :key="item.id">
                        <a :href="item.url"
                           class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 border-b border-gray-50 last:border-b-0 transition">
                            <i :class="group.icon" class="text-gray-400 w-4"></i>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-800 truncate" x-text="item.title"></div>
                                <div class="text-xs text-gray-400 truncate" x-text="item.subtitle || ''"></div>
                            </div>
                            <i class="fas fa-arrow-{{ is_rtl() ? 'left' : 'right' }} text-gray-300 text-xs"></i>
                        </a>
                    </template>
                </div>
            </template>
        </div>
        <div class="px-4 py-2 border-t border-gray-100 bg-gray-50 text-[11px] text-gray-400 flex items-center justify-between" x-show="q.length < 2">
            <span>{{ __('common.search_hint') }}</span>
            <span class="flex items-center gap-2">
                <kbd class="px-1.5 py-0.5 bg-white border border-gray-200 rounded">↑↓</kbd>
                <span>{{ __('common.navigate') }}</span>
                <kbd class="px-1.5 py-0.5 bg-white border border-gray-200 rounded">↵</kbd>
                <span>{{ __('common.open') }}</span>
            </span>
        </div>
    </div>
</div>

<script>
function searchPalette() {
    return {
        open: false,
        q: '',
        loading: false,
        data: {
            projects: [],
            clients: [],
            invoices: [],
            quotes: [],
            tasks: [],
        },
        get groups() {
            const locale = document.documentElement.lang || 'en';
            const isRtl = document.documentElement.dir === 'rtl';
            return [
                { key: 'projects', label: '{{ __('common.projects') }}', icon: 'fas fa-folder', items: this.data.projects.map(p => ({ id: p.id, title: p.name, subtitle: p.project_number, url: '/projects/' + p.id })) },
                { key: 'clients', label: '{{ __('common.clients') }}', icon: 'fas fa-user-tie', items: this.data.clients.map(c => ({ id: c.id, title: c.name, subtitle: c.company_name, url: '/clients/' + c.id })) },
                { key: 'invoices', label: '{{ __('common.invoices') }}', icon: 'fas fa-file-invoice', items: this.data.invoices.map(i => ({ id: i.id, title: i.invoice_number, subtitle: i.total != null ? String(i.total) : '', url: '/invoices/' + i.id })) },
                { key: 'quotes', label: '{{ __('common.quotes') }}', icon: 'fas fa-file-contract', items: this.data.quotes.map(q => ({ id: q.id, title: q.quote_number, subtitle: q.total != null ? String(q.total) : '', url: '/quotes/' + q.id })) },
                { key: 'tasks', label: '{{ __('common.tasks') }}', icon: 'fas fa-tasks', items: this.data.tasks.map(t => ({ id: t.id, title: t.title, subtitle: '', url: '/tasks/' + t.id })) },
            ];
        },
        totalCount() {
            return this.data.projects.length + this.data.clients.length + this.data.invoices.length + this.data.quotes.length + this.data.tasks.length;
        },
        init() {
            this.$watch('open', (val) => {
                if (val) {
                    this.$nextTick(() => this.$refs.searchInput.focus());
                } else {
                    this.q = '';
                }
            });
            // Poll notifications every 60s
            this.startNotificationsPoll();
        },
        async search() {
            if (this.q.length < 2) return;
            this.loading = true;
            try {
                const res = await fetch('{{ route('search') }}?q=' + encodeURIComponent(this.q), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const json = await res.json();
                    this.data = {
                        projects: json.projects || [],
                        clients: json.clients || [],
                        invoices: json.invoices || [],
                        quotes: json.quotes || [],
                        tasks: json.tasks || [],
                    };
                }
            } catch (e) { /* silent */ }
            this.loading = false;
        },
        startNotificationsPoll() {
            const update = async () => {
                try {
                    const res = await fetch('{{ route('notifications.count') }}', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const json = await res.json();
                        const badge = document.getElementById('notifications-badge');
                        if (badge) {
                            if (json.count > 0) {
                                badge.textContent = json.count > 9 ? '9+' : json.count;
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                    }
                } catch (e) { /* silent */ }
            };
            setInterval(update, 60000);
        }
    }
}
</script>
