@extends('portal.layouts.app')

@section('title', __('portal.support_tickets'))

@section('breadcrumb')
    <li class="text-gray-400 mx-1">/</li>
    <li class="text-gray-600 font-medium">{{ __('portal.support_tickets') }}</li>
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('portal.support_tickets') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('portal.support_tickets_hint') }}</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- New ticket --}}
        <div class="card rounded-2xl p-5 lg:order-last">
            <h2 class="mb-4 font-semibold text-gray-900"><i class="fas fa-plus-circle me-2 text-primary-600"></i>{{ __('portal.new_ticket') }}</h2>
            <form method="POST" action="{{ route('portal.tickets.store') }}" class="space-y-3">
                @csrf
                @if($errors->any())
                    <div class="rounded-xl bg-red-50 px-3 py-2 text-xs text-red-700">{{ $errors->first() }}</div>
                @endif
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('portal.subject') }}</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="150" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('portal.priority') }}</label>
                    <select name="priority" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        <option value="low">{{ __('portal.priority_low') }}</option>
                        <option value="normal" selected>{{ __('portal.priority_normal') }}</option>
                        <option value="high">{{ __('portal.priority_high') }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('portal.message') }}</label>
                    <textarea name="message" rows="5" required maxlength="3000" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="w-full rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-primary-700">
                    <i class="fas fa-paper-plane me-2"></i>{{ __('portal.submit_ticket') }}
                </button>
            </form>
        </div>

        {{-- Tickets list --}}
        <div class="space-y-4 lg:col-span-2">
            @forelse($tickets as $ticket)
                @php
                    $sc = ['open' => 'amber', 'answered' => 'emerald', 'closed' => 'gray'][$ticket->status] ?? 'gray';
                @endphp
                <div class="card rounded-2xl p-5">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900">{{ $ticket->subject }}</p>
                            <p class="mt-0.5 text-xs text-gray-400">{{ $ticket->created_at?->format('Y-m-d H:i') }}</p>
                        </div>
                        <span class="rounded-full bg-{{ $sc }}-100 px-3 py-1 text-xs font-medium text-{{ $sc }}-700">{{ __('portal.ticket_status.'.$ticket->status) }}</span>
                    </div>
                    <p class="mt-3 whitespace-pre-line text-sm text-gray-700">{{ $ticket->message }}</p>
                    @if($ticket->admin_reply)
                        <div class="mt-3 rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-semibold text-slate-700">{{ __('portal.team_reply') }} · {{ $ticket->replied_at?->format('Y-m-d H:i') }}</p>
                            <p class="mt-1 whitespace-pre-line text-sm text-slate-800">{{ $ticket->admin_reply }}</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="card rounded-2xl p-16 text-center text-gray-400">
                    <i class="fas fa-life-ring mb-3 block text-4xl text-gray-300"></i>
                    {{ __('portal.no_tickets') }}
                </div>
            @endforelse

            @if($tickets->hasPages())
                <div>{{ $tickets->links() }}</div>
            @endif
        </div>
    </div>
@endsection
