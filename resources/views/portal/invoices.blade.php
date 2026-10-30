@extends('portal.layouts.app')

@section('title', __('finance.invoices'))

@section('breadcrumb')
    <li class="text-gray-400 mx-1">/</li>
    <li class="text-gray-600 font-medium">{{ __('finance.invoices') }}</li>
@endsection

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('finance.invoices') }}</h1>
        <p class="mt-1 text-gray-500">{{ __('portal.all_invoices') }}</p>
    </div>

    <div class="card overflow-hidden rounded-2xl">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50 text-left text-gray-500">
                    <th class="px-5 py-4 font-semibold">{{ __('finance.invoice_number') }}</th>
                    <th class="px-5 py-3 font-semibold hidden md:table-cell">{{ __('project.project') }}</th>
                    <th class="px-5 py-3 font-semibold hidden lg:table-cell">{{ __('finance.invoice_date') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('finance.total') }}</th>
                    <th class="px-5 py-3 font-semibold hidden sm:table-cell">{{ __('finance.remaining') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('messages.status') }}</th>
                    <th class="px-5 py-3 font-semibold text-end">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($invoices as $invoice)
                    @php
                        $sc = ['paid'=>'emerald','partially_paid'=>'amber','overdue'=>'red','unpaid'=>'gray'][$invoice->payment_status] ?? 'gray';
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-4 font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                        <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $invoice->project?->name ?? '-' }}</td>
                        <td class="px-5 py-3 text-gray-500 hidden lg:table-cell">{{ $invoice->invoice_date?->format('Y-m-d') ?? '-' }}</td>
                        <td class="px-5 py-3 font-semibold text-gray-900">{{ number_format($invoice->total, 2) }} {{ __('messages.currency') }}</td>
                        <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ number_format($invoice->remaining_amount, 2) }} {{ __('messages.currency') }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-3 py-1 text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">
                                {{ __('finance.status_'.$invoice->payment_status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-end">
                            <a href="{{ route('portal.invoices.pdf', $invoice) }}" target="_blank" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg inline-block" title="{{ __('messages.download_pdf') }}">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                            <i class="fas fa-file-invoice text-4xl mb-3 block text-gray-300"></i>
                            {{ __('portal.no_invoices') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($invoices->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">{{ $invoices->links() }}</div>
        @endif
    </div>
@endsection