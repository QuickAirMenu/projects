@extends('portal.layouts.app')

@section('title', $invoice->invoice_number)

@section('breadcrumb')
    <li class="text-gray-400 mx-1">/</li>
    <li><a href="{{ route('portal.invoices') }}" class="text-gray-500 hover:text-gray-700">{{ __('finance.invoices') }}</a></li>
    <li class="text-gray-400 mx-1">/</li>
    <li class="text-gray-600 font-medium">{{ $invoice->invoice_number }}</li>
@endsection

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $invoice->invoice_number }}</h1>
            <p class="mt-1 text-gray-500">{{ __('finance.invoice_date') }}: {{ $invoice->invoice_date?->format('Y-m-d') ?? '-' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('portal.invoices.pdf', $invoice) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm text-white transition hover:bg-red-700">
                <i class="fas fa-file-pdf"></i> {{ __('messages.download_pdf') }}
            </a>
            <a href="{{ route('portal.invoices') }}" class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2 text-sm text-gray-700 transition">
                <i class="fas fa-arrow-right"></i> {{ __('messages.back') }}
            </a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Items --}}
            <div class="card overflow-hidden rounded-2xl">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="font-semibold text-gray-900"><i class="fas fa-list me-2 text-primary-600"></i>{{ __('finance.items') }}</h2>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-500">
                            <th class="px-5 py-3 font-semibold">{{ __('finance.description') }}</th>
                            <th class="px-5 py-3 text-center font-semibold">{{ __('finance.quantity') }}</th>
                            <th class="px-5 py-3 text-end font-semibold">{{ __('finance.unit_price') }}</th>
                            <th class="px-5 py-3 text-end font-semibold">{{ __('finance.total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($invoice->items as $item)
                            <tr>
                                <td class="px-5 py-3 text-gray-700">{{ $item->description }}</td>
                                <td class="px-5 py-3 text-center text-gray-600">{{ $item->quantity }}</td>
                                <td class="px-5 py-3 text-end text-gray-600">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-5 py-3 text-end font-medium text-gray-800">{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">{{ __('messages.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="space-y-1 border-t border-gray-100 px-5 py-4 text-sm">
                    <div class="flex justify-between text-gray-600"><span>{{ __('finance.subtotal') }}</span><span>{{ number_format($invoice->subtotal, 2) }} {{ __('messages.currency') }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>{{ __('finance.vat') }}</span><span>{{ number_format($invoice->tax_amount, 2) }} {{ __('messages.currency') }}</span></div>
                    <div class="flex justify-between border-t border-gray-100 pt-2 font-bold text-gray-900"><span>{{ __('finance.total') }}</span><span>{{ number_format($invoice->total, 2) }} {{ __('messages.currency') }}</span></div>
                    <div class="flex justify-between text-emerald-600"><span>{{ __('messages.paid') }}</span><span>{{ number_format($invoice->paid_amount, 2) }} {{ __('messages.currency') }}</span></div>
                    <div class="flex justify-between text-red-600 font-semibold"><span>{{ __('finance.remaining') }}</span><span>{{ number_format($invoice->remaining_amount, 2) }} {{ __('messages.currency') }}</span></div>
                </div>
            </div>

            {{-- Payments --}}
            @if($invoice->payments->count())
            <div class="card overflow-hidden rounded-2xl">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="font-semibold text-gray-900"><i class="fas fa-receipt me-2 text-emerald-600"></i>{{ __('portal.payments') }}</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($invoice->payments as $payment)
                        @php
                            $pvColors = ['pending' => 'amber', 'approved' => 'emerald', 'rejected' => 'red'];
                            $pv = $pvColors[$payment->status] ?? 'gray';
                        @endphp
                        <div class="flex items-start justify-between gap-3 px-5 py-3 text-sm">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-800">{{ $payment->payment_number }} — {{ number_format($payment->amount, 2) }} {{ __('messages.currency') }}</p>
                                <p class="mt-0.5 text-xs text-gray-400">{{ $payment->payment_date?->format('Y-m-d') }} · {{ __('finance.method_'.$payment->payment_method) }}</p>
                                <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-{{ $pv }}-100 px-2.5 py-0.5 text-[11px] font-medium text-{{ $pv }}-700">
                                    <i class="fas {{ $payment->status === 'pending' ? 'fa-hourglass-half' : ($payment->status === 'approved' ? 'fa-circle-check' : 'fa-circle-xmark') }}"></i>
                                    {{ __('portal.payment_status.'.$payment->status) }}
                                </span>
                                @if($payment->status === 'rejected' && $payment->rejection_reason)
                                    <p class="mt-1.5 text-xs text-red-600">{{ __('portal.rejection_reason') }}: {{ $payment->rejection_reason }}</p>
                                @endif
                            </div>
                            @if($payment->proof_file_path)
                                <a href="{{ route('portal.payments.proof', $payment) }}" target="_blank" class="shrink-0 rounded-lg p-1.5 text-primary-600 hover:bg-primary-50" title="{{ __('finance.download_receipt') }}">
                                    <i class="fas fa-paperclip"></i>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="space-y-6">
            {{-- Bank transfer details --}}
            <div class="card rounded-2xl p-5">
                <h2 class="mb-3 font-semibold text-gray-900"><i class="fas fa-building-columns me-2 text-blue-600"></i>{{ __('portal.bank_transfer') }}</h2>
                <p class="mb-4 text-xs text-gray-500">{{ __('portal.bank_transfer_hint') }}</p>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">{{ __('portal.beneficiary') }}</dt><dd class="font-medium text-gray-800 text-end">{{ $bank['bank_account_name'] ?? $bank['company_name_ar'] ?? $bank['company_name'] ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">{{ __('portal.iban') }}</dt><dd class="font-medium text-gray-800 text-end break-all">{{ $bank['bank_iban'] ?? '-' }}</dd></div>
                </dl>
            </div>

            {{-- Upload receipt --}}
            @if($invoice->remaining_amount > 0)
            <div class="card rounded-2xl p-5">
                <h2 class="mb-4 font-semibold text-gray-900"><i class="fas fa-cloud-arrow-up me-2 text-emerald-600"></i>{{ __('portal.upload_receipt') }}</h2>
                <form method="POST" action="{{ route('portal.invoices.receipt', $invoice) }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    @if($errors->any())
                        <div class="rounded-xl bg-red-50 px-3 py-2 text-xs text-red-700">{{ $errors->first() }}</div>
                    @endif
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('portal.amount') }}</label>
                        <input type="number" step="0.01" min="0.01" max="{{ $invoice->remaining_amount }}" name="amount" value="{{ old('amount', $invoice->remaining_amount) }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('portal.payment_date') }}</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('portal.reference') }}</label>
                        <input type="text" name="transaction_reference" value="{{ old('transaction_reference') }}" maxlength="100" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('portal.receipt_file') }}</label>
                        <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.webp,.pdf" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm file:me-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('portal.notes') }}</label>
                        <textarea name="notes" rows="2" maxlength="500" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">
                        <i class="fas fa-paper-plane me-2"></i>{{ __('portal.submit_receipt') }}
                    </button>
                </form>
            </div>
            @else
                <div class="card rounded-2xl p-5 text-center text-sm text-emerald-700">
                    <i class="fas fa-circle-check mb-2 block text-2xl"></i>
                    {{ __('portal.invoice_paid') }}
                </div>
            @endif
        </div>
    </div>
@endsection
