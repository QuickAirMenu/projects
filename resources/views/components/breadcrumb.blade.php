@props(['items' => [], 'showHome' => true])
<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 space-x-reverse">
        @if ($showHome)
            <li>
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-home text-xs"></i>
                </a>
            </li>
        @endif
        @foreach ($items as $index => $item)
            <li class="flex items-center">
                <span class="text-gray-400 mx-1">/</span>
                @if (isset($item['url']) && !$loop->last)
                    <a href="{{ $item['url'] }}" class="text-gray-500 hover:text-gray-700 text-sm">{{ __($item['label']) }}</a>
                @else
                    <span class="text-gray-700 font-medium text-sm">{{ __($item['label']) }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
