<div class="auth-controls">
    <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
       class="auth-ctrl"
       title="{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}"
       aria-label="{{ app()->getLocale() === 'ar' ? 'Switch to English' : 'التبديل إلى العربية' }}">
        <i class="fas fa-language"></i>
        <span>{{ app()->getLocale() === 'ar' ? 'English' : 'عربي' }}</span>
    </a>
    <button type="button" data-auth-theme-toggle class="auth-ctrl auth-ctrl-icon"
            title="{{ __('messages.theme_toggle') }}" aria-label="{{ __('messages.theme_toggle') }}">
        <i class="fas fa-moon"></i>
    </button>
</div>

<style>
    .auth-controls {
        position: fixed; top: 1rem; inset-inline-end: 1rem; z-index: 50;
        display: flex; align-items: center; gap: .5rem;
    }
    .auth-ctrl {
        display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
        height: 38px; padding: 0 .9rem; border-radius: 999px; cursor: pointer;
        background: rgba(255,255,255,.94); color: #374151; font-size: 13px; font-weight: 600;
        border: 1px solid rgba(255,255,255,.7); box-shadow: 0 4px 14px rgba(0,0,0,.12);
        text-decoration: none; transition: background .15s ease, transform .15s ease;
    }
    .auth-ctrl:hover { background: #fff; }
    .auth-ctrl-icon { width: 38px; padding: 0; }
    html.dark .auth-ctrl {
        background: rgba(26,37,55,.94); color: #e2e8f0; border-color: #33465e;
    }
    html.dark .auth-ctrl:hover { background: #22314a; }
</style>

<script>
    (function () {
        var btn = document.querySelector('[data-auth-theme-toggle]');
        if (!btn) return;
        var icon = btn.querySelector('i');
        function paint() {
            var dark = document.documentElement.classList.contains('dark');
            icon.className = dark ? 'fas fa-sun' : 'fas fa-moon';
            btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
        }
        btn.addEventListener('click', function () {
            var dark = document.documentElement.classList.toggle('dark');
            try { localStorage.setItem('ip_theme', dark ? 'dark' : 'light'); } catch (e) {}
            paint();
        });
        paint();
    })();
</script>
