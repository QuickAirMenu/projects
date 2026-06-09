<!-- Footer -->
<footer class="bg-white border-t border-gray-200 py-4 px-6">
    <div class="flex items-center justify-between text-sm text-gray-600">
        <p>
            &copy; {{ date('Y') }} <a href="https://airmenu.net" target="_blank" class="font-semibold text-primary-600 hover:underline">Air Menu</a>. 
            {{ __('common.all_rights_reserved') }}
        </p>
        <p>
            <span class="text-primary-600 font-semibold">{{ config('app.name') }}</span> v1.0.0
        </p>
    </div>
</footer>
