<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-900">
        <i class="fas fa-bell text-xl"></i>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                {{ auth()->user()->unreadNotifications->count() > 9 ? '9+' : auth()->user()->unreadNotifications->count() }}
            </span>
        @endif
    </button>

    <div x-show="open" @click.outside="open = false" x-transition
         class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="font-semibold text-gray-800">{{ __('messages.notifications') }}</h3>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-blue-600 hover:underline">
                        {{ __('messages.mark_all_read') }}
                    </button>
                </form>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse(auth()->user()->notifications->take(10) as $notification)
                <div class="p-3 hover:bg-gray-50 border-b last:border-b-0 {{ is_null($notification->read_at) ? 'bg-blue-50' : '' }}">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-{{ $notification->data['color'] ?? 'blue' }}-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas {{ $notification->data['icon'] ?? 'fa-bell' }} text-{{ $notification->data['color'] ?? 'blue' }}-600 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ $notification->data['title'] ?? '' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $notification->data['message'] ?? '' }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">
                    <i class="fas fa-bell-slash text-3xl mb-2"></i>
                    <p class="text-sm">{{ __('messages.no_notifications') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
