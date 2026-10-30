@extends('layouts.app')
@section('title', __('finance.quotes'))

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'finance.quotes', 'url' => route('quotes.index')]]" :show-home="false" />
@endsection

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('finance.quotes') }}</h1>
    <div class="flex gap-2">
        @if(app()->isLocale('ar'))
        <a href="{{ route('exports.quotes') }}" class="btn-success flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm transition font-medium" title="{{ __('common.export_quotes') }}">
            <i class="fas fa-file-excel"></i>
            <span class="hidden sm:inline">{{ __('common.export') }}</span>
        </a>
        <a href="{{ route('quotes.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm transition font-medium">
            <i class="fas fa-plus me-2"></i>{{ __('finance.add_quote') }}
        </a>
        @else
        <a href="{{ route('quotes.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm transition font-medium">
            <i class="fas fa-plus me-2"></i>{{ __('finance.add_quote') }}
        </a>
        <a href="{{ route('exports.quotes') }}" class="btn-success flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm transition font-medium" title="{{ __('common.export_quotes') }}">
            <i class="fas fa-file-excel"></i>
            <span class="hidden sm:inline">{{ __('common.export') }}</span>
        </a>
        @endif
    </div>
</div>

<div class="flex gap-2 mb-4">
    <a href="{{ route('quotes.index') }}" 
       class="px-4 py-2 rounded-xl text-sm font-medium transition {{ !$trashed ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        <i class="fas fa-file-alt me-1"></i>{{ __('messages.all') }}
    </a>
    <a href="{{ route('quotes.index', ['trashed' => 1]) }}" 
       class="px-4 py-2 rounded-xl text-sm font-medium transition {{ $trashed ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        <i class="fas fa-archive me-1"></i>{{ __('messages.archive') }}
    </a>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => __('messages.total'), 'val' => $stats['total'], 'meta' => __('finance.all_quotes'), 'icon' => 'fa-file-alt', 'chip' => 'bg-blue-50 text-blue-600'],
        ['label' => __('finance.unconverted_value'), 'val' => number_format($stats['unconverted_amount'], 2) . ' ' . __('messages.currency'), 'meta' => $stats['unconverted_count'] . ' ' . __('finance.quote_count'), 'icon' => 'fa-file-invoice', 'chip' => 'bg-amber-50 text-amber-600'],
        ['label' => __('finance.approved_unconverted'), 'val' => number_format($stats['approved_unconverted_amount'], 2) . ' ' . __('messages.currency'), 'meta' => $stats['expired_unconverted_count'] . ' ' . __('finance.expired_ones'), 'icon' => 'fa-clock', 'chip' => 'bg-emerald-50 text-emerald-600'],
        ['label' => __('finance.conversion_rate'), 'val' => $stats['conversion_rate'] . '%', 'meta' => __('finance.of_total_quotes'), 'icon' => 'fa-exchange-alt', 'chip' => 'bg-slate-100 text-slate-600'],
    ] as $card)
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $card['label'] }}</p>
                <p class="mt-2 text-xl font-bold text-gray-900 break-words privacy-mask">{{ $card['val'] }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ $card['meta'] }}</p>
            </div>
            <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $card['chip'] }}">
                <i class="fas {{ $card['icon'] }}"></i>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('finance.quote_number') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">{{ __('client.client') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">{{ __('messages.status') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('finance.total') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">{{ __('finance.valid_until') }}</th>
                <th class="text-end px-5 py-3 font-semibold text-gray-600">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($quotes as $quote)
            @php
                $statusClasses = ['draft' => 'bg-gray-100 text-gray-700', 'sent' => 'bg-blue-100 text-blue-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700'][$quote->status] ?? 'bg-gray-100 text-gray-700';
                $expired = $quote->isExpired;
            @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3 font-medium text-gray-800">
                    <a href="{{ route('quotes.show', $quote) }}" class="hover:text-blue-600 privacy-mask">{{ $quote->quote_number }}</a>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $quote->client?->full_name }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusClasses }}">{{ __('finance.quote_status.' . $quote->status) }}</span>
                    @if($expired)
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700 ms-1">{{ __('finance.expired') }}</span>
                    @endif
                </td>
                <td class="px-4 py-3 hidden lg:table-cell font-medium privacy-mask">{{ number_format($quote->total, 2) }}</td>
                <td class="px-4 py-3 hidden xl:table-cell {{ $expired ? 'text-red-500 font-medium' : 'text-gray-500' }}">{{ $quote->valid_until?->format('Y-m-d') ?? '-' }}</td>
                <td class="px-5 py-3 text-end">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('quotes.show', $quote) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('quotes.public', $quote->public_token) }}" target="_blank" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="{{ __('messages.public_link') }}"><i class="fas fa-link"></i></a>
                        @if($trashed)
                        <form action="{{ route('quotes.restore', $quote) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg" title="{{ __('messages.restore') }}">
                                <i class="fas fa-undo"></i>
                            </button>
                        </form>
                        @else
                        <a href="{{ route('quotes.edit', $quote) }}" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg"><i class="fas fa-edit"></i></a>
                        @if($quote->status === 'draft')
                        <form action="{{ route('quotes.send', $quote) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg" title="{{ __('messages.send') }}"><i class="fas fa-paper-plane"></i></button>
                        </form>
                        @endif
                        <form action="{{ route('quotes.destroy', $quote) }}" method="POST" onsubmit="return confirmDelete(this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-16 text-center text-gray-400">
                <i class="fas fa-file-alt text-5xl mb-3 block text-gray-200"></i>
                {{ __('finance.no_quotes') }}
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($quotes->hasPages())
    <div class="px-5 py-4 border-t">{{ $quotes->links() }}</div>
    @endif
</div>
@endsection
