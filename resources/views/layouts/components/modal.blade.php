{{-- Usage: <x-modal id="myModal" title="عنوان">...</x-modal> --}}
@props(['id', 'title' => '', 'size' => 'md'])

@php
$sizes = ['sm' => 'max-w-sm', 'md' => 'max-w-lg', 'lg' => 'max-w-2xl', 'xl' => 'max-w-4xl'];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden" x-data="{ open: false }"
     :class="{ 'flex': open, 'hidden': !open }"
     x-on:open-modal.window="if ($event.detail === '{{ $id }}') open = true"
     x-on:close-modal.window="open = false">
    <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
    <div class="relative z-10 m-auto w-full {{ $sizeClass }} bg-white rounded-2xl shadow-2xl">
        <div class="flex items-center justify-between p-5 border-b">
            <h3 class="text-lg font-bold text-gray-800">{{ $title }}</h3>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600 text-xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-5">{{ $slot }}</div>
    </div>
</div>
