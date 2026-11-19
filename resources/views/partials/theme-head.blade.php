<script>
    (function () {
        try {
            if (localStorage.getItem('ip_theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {}
    })();
</script>
<link rel="stylesheet" href="{{ asset('css/dark.css?v=3') }}">
