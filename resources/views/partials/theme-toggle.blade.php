<button type="button" data-theme-toggle aria-label="{{ __('messages.theme_toggle') }}"
        class="fixed bottom-6 {{ is_rtl() ? 'left-6' : 'right-6' }} z-50 flex h-11 w-11 items-center justify-center rounded-full bg-gray-900 text-white shadow-lg hover:opacity-90">
    <i class="fas fa-moon"></i>
</button>
<script>
    (function () {
        const buttons = Array.from(document.querySelectorAll('#themeToggle,[data-theme-toggle]'));
        if (!buttons.length) return;
        function paint() {
            const dark = document.documentElement.classList.contains('dark');
            buttons.forEach(function (btn) {
                const label = btn.querySelector('[data-theme-label]');
                const icon = btn.querySelector('i');
                if (label) label.textContent = dark ? @json(__('messages.theme_light')) : @json(__('messages.theme_dark'));
                if (icon) icon.className = dark ? 'fas fa-sun w-4 text-center' : 'fas fa-moon w-4 text-center';
            });
        }
        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const dark = document.documentElement.classList.toggle('dark');
                try { localStorage.setItem('ip_theme', dark ? 'dark' : 'light'); } catch (e) {}
                paint();
            });
        });
        paint();
    })();
</script>
