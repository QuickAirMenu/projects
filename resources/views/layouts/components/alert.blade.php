@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
     class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-4">
    <i class="fas fa-check-circle text-green-500"></i>
    <span class="text-sm font-medium">{{ session('success') }}</span>
    <button @click="show = false" class="ms-auto text-green-400 hover:text-green-600"><i class="fas fa-times"></i></button>
</div>
@endif

@if(session('error'))
<div x-data="{ show: true }" x-show="show" x-transition
     class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-4">
    <i class="fas fa-exclamation-circle text-red-500"></i>
    <span class="text-sm font-medium">{{ session('error') }}</span>
    <button @click="show = false" class="ms-auto text-red-400 hover:text-red-600"><i class="fas fa-times"></i></button>
</div>
@endif

@if(isset($errors) && $errors->any())
<div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-4">
    <div class="flex items-center gap-2 mb-2">
        <i class="fas fa-exclamation-triangle text-red-500"></i>
        <span class="text-sm font-semibold">{{ __('validation.failed') }}</span>
    </div>
    <ul class="text-sm list-disc list-inside space-y-1">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
