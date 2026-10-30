@extends('portal.layouts.app')

@section('title', __('finance.quotes'))

@section('breadcrumb')
    <li class="text-gray-400 mx-1">/</li>
    <li class="text-gray-600 font-medium">{{ __('finance.quotes') }}</li>
@endsection

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('finance.quotes') }}</h1>
        <p class="mt-1 text-gray-500">{{ __('portal.all_quotes') }}</p>
    </div>

    <div class="card overflow-hidden rounded-2xl">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50 text-left text-gray-500">
                    <th class="px-5 py-4 font-semibold">{{ __('finance.quote_number') }}</th>
                    <th class="px-5 py-3 font-semibold hidden md:table-cell">{{ __('finance.subject') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('finance.total') }}</th>
                    <th class="px-5 py-3 font-semibold hidden lg:table-cell">{{ __('finance.valid_until') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('messages.status') }}</th>
                    <th class="px-5 py-3 font-semibold text-end">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($quotes as $quote)
                    @php
                        $sc = ['draft'=>'gray','sent'=>'blue','approved'=>'emerald','rejected'=>'red'][$quote->status] ?? 'gray';
                        $expired = $quote->valid_until && $quote->valid_until->isPast();
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-4 font-medium text-gray-900">{{ $quote->quote_number }}</td>
                        <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $quote->subject ?? '-' }}</td>
                        <td class="px-5 py-3 font-semibold text-gray-900">{{ number_format($quote->total, 2) }} {{ __('messages.currency') }}</td>
                        <td class="px-5 py-3 text-gray-500 hidden lg:table-cell {{ $expired ? 'text-red-500' : '' }}">{{ $quote->valid_until?->format('Y-m-d') ?? '-' }}</td>
                        <td class="px-5 py-3 space-x-1">
                            <span class="rounded-full px-3 py-1 text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">
                                {{ __('finance.quote_status.'.$quote->status) }}
                            </span>
                            @if($expired)
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-medium bg-orange-100 text-orange-700">{{ __('finance.expired') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-end">
                            <div class="flex items-center justify-end gap-1">
                                @if($quote->status !== 'draft' && $quote->public_token)
                                    <a href="{{ route('quotes.public', $quote->public_token) }}" target="_blank" class="p-1.5 text-primary-600 hover:bg-primary-50 rounded-lg" title="{{ __('messages.public_link') }}">
                                        <i class="fas fa-link"></i>
                                    </a>
                                @endif
                                <a href="{{ route('portal.quotes.pdf', $quote) }}" target="_blank" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg" title="{{ __('messages.download_pdf') }}">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center text-gray-400">
                            <i class="fas fa-file-alt text-4xl mb-3 block text-gray-300"></i>
                            {{ __('portal.no_quotes') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($quotes->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">{{ $quotes->links() }}</div>
        @endif
    </div>
@endsection