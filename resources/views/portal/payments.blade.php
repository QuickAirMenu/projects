@extends('portal.layouts.app')

@section('title', __('finance.payments'))

@section('breadcrumb')
    <li class="text-gray-400 mx-1">/</li>
    <li class="text-gray-600 font-medium">{{ __('finance.payments') }}</li>
@endsection

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('finance.payments') }}</h1>
        <p class="mt-1 text-gray-500">{{ __('finance.all_payments') }}</p>
    </div>

    <div class="card overflow-hidden rounded-2xl">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50 text-left text-gray-500">
                    <th class="px-5 py-4 font-semibold">{{ __('finance.payment_number') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('finance.invoice') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('finance.amount') }}</th>
                    <th class="px-5 py-3 font-semibold hidden md:table-cell">{{ __('finance.payment_date') }}</th>
                    <th class="px-5 py-3 font-semibold hidden sm:table-cell">{{ __('finance.payment_method') }}</th>
                    <th class="px-5 py-3 font-semibold hidden lg:table-cell">{{ __('finance.reference') }}</th>
                    <th class="px-5 py-3 font-semibold text-end">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-4 font-medium text-gray-900">{{ $payment->payment_number }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $payment->invoice?->invoice_number ?? '-' }}</td>
                        <td class="px-5 py-3 font-semibold text-emerald-700">{{ number_format($payment->amount, 2) }} {{ __('messages.currency') }}</td>
                        <td class="px-5 py-3 text-gray-500 hidden md:table-cell">{{ $payment->payment_date?->format('Y-m-d') ?? '-' }}</td>
                        <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $payment->payment_method_text ?? $payment->payment_method }}</td>
                        <td class="px-5 py-3 text-gray-500 hidden lg:table-cell">{{ $payment->transaction_reference ?? '-' }}</td>
                        <td class="px-5 py-3 text-end">
                            @if($payment->proof_file_path)
                                <a href="{{ route('portal.payments.proof', $payment) }}" target="_blank" class="p-1.5 text-primary-600 hover:bg-primary-50 rounded-lg inline-block" title="{{ __('finance.download_receipt') }}">
                                    <i class="fas fa-download"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                            <i class="fas fa-money-bill-wave text-4xl mb-3 block text-gray-300"></i>
                            {{ __('finance.no_payments') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($payments->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">{{ $payments->links() }}</div>
        @endif
    </div>
@endsection