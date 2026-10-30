{{-- 
    Backward-compatible inline alert banner.
    For new code, prefer flashing session messages and using <x-toast-container />.
    Usage (legacy): @include('layouts.components.alert')
--}}

@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
     class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-4"
     role="alert">
    <i class="fas fa-check-circle text-green-500"></i>
    <span class="text-sm font-medium">{{ session('success') }}</span>
    <button @click="show = false" class="ms-auto text-green-400 hover:text-green-600" aria-label="{{ __('messages.close') }}"><i class="fas fa-times"></i></button>
</div>
@endif

@if(session('error'))
<div x-data="{ show: true }" x-show="show" x-transition
     class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-4"
     role="alert">
    <i class="fas fa-times-circle text-red-500"></i>
    <span class="text-sm font-medium">{{ session('error') }}</span>
    <button @click="show = false" class="ms-auto text-red-400 hover:text-red-600" aria-label="{{ __('messages.close') }}"><i class="fas fa-times"></i></button>
</div>
@endif

@if(session('warning'))
<div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
     class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 mb-4"
     role="alert">
    <i class="fas fa-exclamation-triangle text-amber-500"></i>
    <span class="text-sm font-medium">{{ session('warning') }}</span>
    <button @click="show = false" class="ms-auto text-amber-400 hover:text-amber-600" aria-label="{{ __('messages.close') }}"><i class="fas fa-times"></i></button>
</div>
@endif

@if(session('info'))
<div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
     class="flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-4 py-3 mb-4"
     role="alert">
    <i class="fas fa-info-circle text-blue-500"></i>
    <span class="text-sm font-medium">{{ session('info') }}</span>
    <button @click="show = false" class="ms-auto text-blue-400 hover:text-blue-600" aria-label="{{ __('messages.close') }}"><i class="fas fa-times"></i></button>
</div>
@endif
