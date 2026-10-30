<div id="toast-container" class="fixed top-4 right-4 z-[9999] space-y-2">
    @if (session('success'))
        <div class="bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 min-w-[300px]">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 min-w-[300px]">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if (session('warning'))
        <div class="bg-yellow-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 min-w-[300px]">
            <i class="fas fa-exclamation-triangle"></i>
            <span>{{ session('warning') }}</span>
        </div>
    @endif
    @if (session('info'))
        <div class="bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 min-w-[300px]">
            <i class="fas fa-info-circle"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif
</div>
<script>
setTimeout(function() {
    document.querySelectorAll('#toast-container > div').forEach(function(el) {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(function() { el.remove(); }, 500);
    });
}, 5000);
</script>
