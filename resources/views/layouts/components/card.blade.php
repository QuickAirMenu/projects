{{-- Usage: <x-card title="عنوان" icon="fa-users"> ... </x-card> --}}
@props(['title' => '', 'icon' => '', 'color' => 'blue', 'actions' => null])

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    @if($title)
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
            @if($icon)
                <div class="w-8 h-8 rounded-lg bg-{{ $color }}-100 flex items-center justify-center">
                    <i class="fas {{ $icon }} text-{{ $color }}-600 text-sm"></i>
                </div>
            @endif
            <h3 class="font-semibold text-gray-800">{{ $title }}</h3>
        </div>
        @if($actions)
            <div>{{ $actions }}</div>
        @endif
    </div>
    @endif
    <div class="p-5">{{ $slot }}</div>
</div>
