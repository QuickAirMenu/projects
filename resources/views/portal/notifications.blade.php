@extends('portal.layouts.app')

@section('title', __('portal.notifications'))

@section('breadcrumb')
    <li class="text-gray-400 mx-1">/</li>
    <li class="text-gray-600 font-medium">{{ __('portal.notifications') }}</li>
@endsection

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('portal.notifications') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('portal.unread') }}: <span class="font-semibold text-primary-600">{{ $unreadCount }}</span></p>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('portal.notifications.read-all') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-200">
                    <i class="fas fa-check-double"></i> {{ __('portal.mark_all_read') }}
                </button>
            </form>
        @endif
    </div>

    <div class="card overflow-hidden rounded-2xl">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = is_null($notification->read_at);
                $color = $data['color'] ?? 'blue';
            @endphp
            <div class="flex items-start gap-4 border-b border-gray-100 px-5 py-4 last:border-0 {{ $isUnread ? 'bg-blue-50/40' : '' }}">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-{{ $color }}-100">
                    <i class="fas {{ $data['icon'] ?? 'fa-bell' }} text-{{ $color }}-600"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-semibold text-gray-800">{{ $data['title'] ?? '' }}</p>
                        @if($isUnread)
                            <span class="rounded-full bg-primary-100 px-2 py-0.5 text-[10px] font-medium text-primary-700">{{ __('portal.unread') }}</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-sm text-gray-600">{{ $data['message'] ?? '' }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $notification->created_at?->diffForHumans() }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-1">
                    @if(! empty($data['url']))
                        <a href="{{ $data['url'] }}" class="rounded-lg p-2 text-primary-600 hover:bg-primary-50" title="{{ __('portal.view') }}">
                            <i class="fas fa-arrow-up-right-from-square"></i>
                        </a>
                    @endif
                    @if($isUnread)
                        <form method="POST" action="{{ route('portal.notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100" title="{{ __('portal.mark_read') }}">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="px-5 py-16 text-center text-gray-400">
                <i class="fas fa-bell-slash mb-3 block text-4xl text-gray-300"></i>
                {{ __('portal.no_notifications') }}
            </div>
        @endforelse
        @if($notifications->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
