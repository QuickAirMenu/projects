@extends('layouts.app')

@section('title', __('messages.support_tickets'))

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'messages.support_tickets', 'url' => route('support-tickets.index')]]" :show-home="false" />
@endsection

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.support_tickets') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('messages.open_tickets') }}: <span class="font-semibold text-amber-600">{{ $openCount }}</span></p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <select name="status" class="rounded-xl border border-gray-300 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">{{ __('messages.all') }}</option>
                @foreach(['open', 'answered', 'closed'] as $st)
                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ __('messages.ticket_status.'.$st) }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($tickets as $ticket)
            @php
                $sc = ['open' => 'amber', 'answered' => 'emerald', 'closed' => 'gray'][$ticket->status] ?? 'gray';
            @endphp
            <div class="card rounded-2xl p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900">{{ $ticket->subject }}</p>
                        <p class="mt-0.5 text-xs text-gray-400">{{ $ticket->client?->name }} · {{ $ticket->created_at?->format('Y-m-d H:i') }}</p>
                    </div>
                    <span class="rounded-full bg-{{ $sc }}-100 px-3 py-1 text-xs font-medium text-{{ $sc }}-700">{{ __('messages.ticket_status.'.$ticket->status) }}</span>
                </div>

                <p class="mt-3 whitespace-pre-line text-sm text-gray-700">{{ $ticket->message }}</p>

                @if($ticket->admin_reply)
                    <div class="mt-4 rounded-xl bg-emerald-50 p-4">
                        <p class="text-xs font-semibold text-emerald-800">{{ __('messages.reply') }} — {{ $ticket->replier?->name ?? '-' }}</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-emerald-900">{{ $ticket->admin_reply }}</p>
                    </div>
                @endif

                <div class="mt-4 flex flex-wrap items-end gap-3">
                    <form method="POST" action="{{ route('support-tickets.reply', $ticket) }}" class="flex-1 min-w-[260px]">
                        @csrf
                        <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('messages.reply') }}</label>
                        <textarea name="admin_reply" rows="2" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">{{ $ticket->admin_reply }}</textarea>
                        <button type="submit" class="mt-2 rounded-xl bg-primary-600 px-4 py-2 text-xs font-medium text-white hover:bg-primary-700">
                            <i class="fas fa-paper-plane me-1"></i>{{ __('messages.send_reply') }}
                        </button>
                    </form>
                    @if($ticket->status !== 'closed')
                        <form method="POST" action="{{ route('support-tickets.close', $ticket) }}">
                            @csrf
                            <button type="submit" class="rounded-xl bg-gray-100 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-200">
                                <i class="fas fa-lock me-1"></i>{{ __('messages.close_ticket') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="card rounded-2xl p-16 text-center text-gray-400">
                <i class="fas fa-life-ring mb-3 block text-4xl text-gray-300"></i>
                {{ __('messages.no_tickets') }}
            </div>
        @endforelse
    </div>

    @if($tickets->hasPages())
        <div class="mt-4">{{ $tickets->links() }}</div>
    @endif
@endsection
