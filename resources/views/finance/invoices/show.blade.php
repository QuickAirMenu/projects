@extends('layouts.app')
@section('title', $invoice->invoice_number)

@section('content')
<div class="mb-5 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $invoice->invoice_number }}</h1>
        <p class="text-gray-500 text-sm">{{ $invoice->client->name }} · {{ $invoice->project->name }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('invoices.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}</a>
    </div>
</div>
@include('layouts.components.alert')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between mb-6 text-sm text-gray-500">
                <div><p class="font-bold text-gray-800 text-lg">{{ config('app.name') }}</p></div>
                <div class="text-end">
                    <p><strong>{{ __('finance.invoice_number') }}:</strong> {{ $invoice->invoice_number }}</p>
                    <p><strong>{{ __('finance.invoice_date') }}:</strong> {{ $invoice->invoice_date->format('Y-m-d') }}</p>
                    @if($invoice->due_date)<p><strong>{{ __('finance.due_date') }}:</strong> {{ $invoice->due_date->format('Y-m-d') }}</p>@endif
                </div>
            </div>
            <table class="w-full text-sm mb-6">
                <thead><tr class="bg-gray-50"><th class="text-start p-3 rounded-s-lg">{{ __('messages.description') }}</th><th class="text-center p-3">كمية</th><th class="text-end p-3 rounded-e-lg">{{ __('finance.total') }}</th></tr></thead>
                <tbody>
                @foreach($invoice->items as $item)
                <tr class="border-b"><td class="p-3">{{ $item->description }}</td><td class="p-3 text-center">{{ $item->quantity }} × {{ number_format($item->unit_price, 2) }}</td><td class="p-3 text-end font-medium">{{ number_format($item->total, 2) }}</td></tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="2" class="p-3 text-end text-gray-500">{{ __('finance.subtotal') }}</td><td class="p-3 text-end">{{ number_format($invoice->subtotal, 2) }}</td></tr>
                    <tr><td colspan="2" class="p-3 text-end text-gray-500">{{ __('finance.tax') }} ({{ $invoice->tax_percentage }}%)</td><td class="p-3 text-end">{{ number_format($invoice->tax_amount, 2) }}</td></tr>
                    <tr class="border-t"><td colspan="2" class="p-3 text-end font-bold">{{ __('finance.total') }}</td><td class="p-3 text-end font-bold text-blue-600 text-lg">{{ number_format($invoice->total, 2) }}</td></tr>
                </tfoot>
            </table>
        </div>

        {{-- Payments --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 mb-4">{{ __('finance.payments') }}</h3>
            @if($invoice->payment_status !== 'paid')
            <form action="{{ route('payments.store') }}" method="POST" class="grid grid-cols-4 gap-2 mb-4">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                <input type="number" name="amount" step="0.01" placeholder="{{ __('finance.paid_amount') }}" required
                       class="col-span-1 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required
                       class="col-span-1 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="payment_method" required class="col-span-1 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['bank_transfer','cash','cheque','card','online','other'] as $m)
                        <option value="{{ $m }}">{{ __('finance.method_'.$m) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="col-span-1 bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-2 rounded-xl transition">
                    <i class="fas fa-plus me-1"></i>{{ __('finance.add_payment') }}
                </button>
            </form>
            @endif
            @forelse($invoice->payments as $payment)
            <div class="flex items-center justify-between py-3 border-b last:border-0 text-sm">
                <div>
                    <p class="font-medium text-gray-800">{{ number_format($payment->amount, 2) }} {{ __('messages.currency') }}</p>
                    <p class="text-xs text-gray-400">{{ __('finance.method_'.$payment->payment_method) }} · {{ $payment->payment_date->format('Y-m-d') }}</p>
                </div>
                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">{{ $payment->payment_number }}</span>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-3">لا توجد دفعات مسجلة</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-sm space-y-3">
            <div class="flex justify-between"><span class="text-gray-500">{{ __('finance.status_unpaid') }}</span>
                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $invoice->payment_status === 'paid' ? 'bg-green-100 text-green-700' : ($invoice->payment_status === 'partially_paid' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                    {{ __('finance.status_'.$invoice->payment_status) }}
                </span>
            </div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('finance.total') }}</span><span class="font-bold">{{ number_format($invoice->total, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('finance.paid_amount') }}</span><span class="text-green-600 font-medium">{{ number_format($invoice->paid_amount, 2) }}</span></div>
            <div class="flex justify-between border-t pt-2"><span class="font-semibold">{{ __('finance.remaining') }}</span><span class="font-bold text-red-500">{{ number_format($invoice->remaining_amount, 2) }}</span></div>
        </div>
    </div>
</div>
@endsection
