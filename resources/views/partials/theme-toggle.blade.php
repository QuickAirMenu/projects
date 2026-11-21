<footer class="auth-footer">
    <div class="auth-footer-inner">
        <span class="auth-footer-copy">
            &copy; {{ date('Y') }} <a href="{{ url('/') }}">{{ config('app.name') }}</a>
        </span>
        <nav class="auth-footer-links" aria-label="{{ __('legal.legal_links') }}">
            <a href="{{ route('privacy') }}">{{ __('legal.privacy.title') }}</a>
            <span aria-hidden="true">·</span>
            <a href="{{ route('terms') }}">{{ __('legal.terms.title') }}</a>
        </nav>
        <div class="auth-footer-actions">
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
    </div>
</footer>

<style>
    body { padding-bottom: 5.5rem; }
    .auth-footer {
        position: fixed; inset-inline: 0; bottom: 0; z-index: 50;
        background: rgba(255,255,255,.92); border-top: 1px solid rgba(0,0,0,.06);
        backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    }
    .auth-footer-inner {
        max-width: 72rem; margin: 0 auto; padding: .7rem 1rem;
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .6rem;
        font-size: 12px; color: #6b7280;
    }
    .auth-footer-copy a { color: #4b5563; font-weight: 600; text-decoration: none; }
    .auth-footer-links { display: flex; align-items: center; gap: .5rem; }
    .auth-footer-links a { color: #4b5563; text-decoration: none; }
    .auth-footer-links a:hover { color: #2563eb; text-decoration: underline; }
    .auth-footer-actions { display: flex; align-items: center; gap: .5rem; }
    .auth-ctrl {
        display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
        height: 34px; padding: 0 .8rem; border-radius: 999px; cursor: pointer;
        background: #f3f4f6; color: #374151; font-size: 12px; font-weight: 600;
        border: 1px solid #e5e7eb; text-decoration: none;
    }
    .auth-ctrl:hover { background: #e5e7eb; }
    .auth-ctrl-icon { width: 34px; padding: 0; }
    html.dark body { background-color: #0f172a; }
    html.dark .auth-footer { background: rgba(17,24,39,.94); border-top-color: #26364c; }
    html.dark .auth-footer-inner { color: #94a3b8; }
    html.dark .auth-footer-copy a,
    html.dark .auth-footer-links a { color: #cbd5e1; }
    html.dark .auth-footer-links a:hover { color: #93c5fd; }
    html.dark .auth-ctrl { background: #1a2537; color: #e2e8f0; border-color: #33465e; }
    html.dark .auth-ctrl:hover { background: #22314a; }
    @media (max-width: 640px) {
        .auth-footer-inner { justify-content: center; text-align: center; }
    }
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
