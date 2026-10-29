@extends('layouts.app')
@section('title', $contract->contract_number)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'finance.contracts', 'url' => route('contracts.index')],
        ['label' => $contract->contract_number, 'url' => null],
    ]" :show-home="false" />
@endsection

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $contract->title }}</h1>
        <p class="text-gray-500 text-sm mt-1 privacy-mask">{{ $contract->contract_number }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('contracts.pdf', $contract) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-file-pdf me-2"></i>{{ __('messages.download_pdf') }}</a>
        <a href="{{ route('contracts.edit', $contract) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-edit me-2"></i>{{ __('messages.edit') }}</a>
        <a href="{{ route('contracts.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-2 gap-5 text-sm mb-5">
            <div><p class="text-gray-500 mb-1">{{ __('finance.project') }}</p><a href="{{ route('projects.show', $contract->project) }}" class="font-medium text-blue-600 hover:underline">{{ $contract->project?->name }}</a></div>
            <div><p class="text-gray-500 mb-1">{{ __('client.client') }}</p><p class="font-medium">{{ $contract->client?->full_name }}</p></div>
            @if($contract->invoice)
            <div><p class="text-gray-500 mb-1">{{ __('finance.linked_invoice') }}</p><a href="{{ route('invoices.show', $contract->invoice) }}" class="font-medium text-blue-600 hover:underline">{{ $contract->invoice->invoice_number }}</a></div>
            @endif
            <div><p class="text-gray-500 mb-1">{{ __('finance.contract_value_label') }}</p><p class="font-bold text-xl text-gray-800 privacy-mask">{{ number_format($contract->contract_value,2) }} {{ __('messages.currency') }}</p></div>
            <div><p class="text-gray-500 mb-1">{{ __('messages.status') }}</p>
                @php $sc = ['draft'=>'gray','active'=>'green','completed'=>'blue','terminated'=>'red'][$contract->status]??'gray' @endphp
                <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ __('finance.contract_status.'.$contract->status) }}</span>
            </div>
            <div><p class="text-gray-500 mb-1">{{ __('finance.start_date') }}</p><p class="font-medium">{{ \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') }}</p></div>
            <div><p class="text-gray-500 mb-1">{{ __('finance.end_date') }}</p><p class="font-medium">{{ $contract->end_date ? \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d') : '-' }}</p></div>
        </div>
        @if($contract->description)
        <div class="border-t pt-4">
            <h3 class="font-semibold text-gray-700 mb-2">{{ __('messages.description') }}</h3>
            <p class="text-gray-600 text-sm whitespace-pre-line">{{ $contract->description }}</p>
        </div>
        @endif
        @if($contract->deliverables)
        <div class="border-t pt-4">
            <h3 class="font-semibold text-gray-700 mb-2">{{ __('finance.deliverables') }}</h3>
            <p class="text-gray-600 text-sm whitespace-pre-line">{{ $contract->deliverables }}</p>
        </div>
        @endif
        @if($contract->payment_terms)
        <div class="border-t pt-4">
            <h3 class="font-semibold text-gray-700 mb-2">{{ __('finance.payment_terms_label') }}</h3>
            <p class="text-gray-600 text-sm">{{ $contract->payment_terms }}</p>
        </div>
        @endif
        @if($contract->terms_and_conditions)
        <div class="border-t pt-4">
            <h3 class="font-semibold text-gray-700 mb-2">{{ __('finance.terms_and_conditions') }}</h3>
            <p class="text-gray-600 text-sm whitespace-pre-line">{{ $contract->terms_and_conditions }}</p>
        </div>
        @endif
        @if($contract->contract_file_path)
        <div class="border-t pt-4 mt-4">
            <a href="{{ route('contracts.download', $contract) }}" class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2 rounded-xl text-sm hover:bg-blue-100 transition">
                <i class="fas fa-file-pdf"></i> {{ __('finance.download_contract') }}
            </a>
        </div>
        @endif
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-sm space-y-3 h-fit">
        <div><p class="text-gray-500 mb-1">{{ __('messages.created_by') }}</p><p class="font-medium">{{ $contract->creator?->name }}</p></div>
        <div><p class="text-gray-500 mb-1">{{ __('messages.created_at') }}</p><p>{{ $contract->created_at->format('Y-m-d') }}</p></div>
        @if($contract->signed_by_client)
        <div class="flex items-center gap-2 text-green-600"><i class="fas fa-check-circle"></i><span>{{ __('finance.signed_by_client_label') }}</span></div>
        @if($contract->signed_at)
        <div class="text-gray-500">{{ __('finance.signed_date') }}: {{ $contract->signed_at->format('Y-m-d') }}</div>
        @endif
        @endif
    </div>
</div>

<div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <div class="flex items-center justify-between gap-4 mb-4">
        <h3 class="font-semibold text-gray-800">{{ __('finance.project_invoices') }}</h3>
        <div class="text-sm text-gray-500">
            {{ __('finance.total_invoices_label') }}: <span class="font-semibold text-gray-800 privacy-mask">{{ number_format($projectInvoices->sum('total'), 2) }}</span>
            <span class="mx-2">|</span>
            {{ __('finance.against_contract_value') }}: <span class="font-semibold text-gray-800 privacy-mask">{{ number_format($contract->contract_value, 2) }}</span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-gray-50 text-gray-600">
                    <th class="px-4 py-3 text-start">{{ __('finance.invoice_number') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('finance.amount_th') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('finance.paid_th') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('finance.remaining_th') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('finance.status_th') }}</th>
                    <th class="px-4 py-3 text-end">{{ __('finance.link') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($projectInvoices as $invoice)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $invoice->invoice_number }}</td>
                    <td class="px-4 py-3 privacy-mask">{{ number_format($invoice->total, 2) }}</td>
                    <td class="px-4 py-3 text-green-600 privacy-mask">{{ number_format($invoice->paid_amount, 2) }}</td>
                    <td class="px-4 py-3 text-red-600 privacy-mask">{{ number_format($invoice->remaining_amount, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $invoice->payment_status === 'paid' ? 'bg-green-100 text-green-700' : ($invoice->payment_status === 'partially_paid' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                            {{ __('finance.status_'.$invoice->payment_status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-end">
                        <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:underline">{{ __('messages.view') }}</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">{{ __('finance.no_project_invoices') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
