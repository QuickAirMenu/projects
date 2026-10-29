@extends('layouts.app')
@section('title', $quote->quote_number)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'finance.quotes', 'url' => route('quotes.index')],
        ['label' => $quote->quote_number, 'url' => null],
    ]" :show-home="false" />
@endsection

@section('content')
<div class="mb-5 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 privacy-mask">{{ $quote->quote_number }}</h1>
        <p class="text-gray-500 text-sm">{{ $quote->client?->full_name }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('quotes.pdf', $quote) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-file-pdf me-2"></i>{{ __('messages.download_pdf') }}</a>
        <a href="{{ route('quotes.public', $quote->public_token) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-link me-2"></i>{{ __('messages.public_link') }}</a>
        <a href="{{ route('quotes.edit', $quote) }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-edit me-2"></i>{{ __('messages.edit') }}</a>
        @if($quote->status === 'draft')
        <form action="{{ route('quotes.send', $quote) }}" method="POST">
            @csrf
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-paper-plane me-2"></i>{{ __('messages.send') }}</button>
        </form>
        @endif
        @if($quote->status === 'sent')
        <form action="{{ route('quotes.approve', $quote) }}" method="POST">
            @csrf
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-check-circle me-2"></i>{{ __('finance.approve_quote') }}</button>
        </form>
        @endif
        @if($quote->status === 'draft' && $whatsappUrl)
        <form action="{{ route('quotes.send-whatsapp', $quote) }}" method="POST">
            @csrf
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-xl text-sm transition"><i class="fab fa-whatsapp me-2"></i>{{ __('finance.send_via_whatsapp') }}</button>
        </form>
        @endif
        <a href="{{ route('quotes.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between mb-6 text-sm">
                <div><p class="font-bold text-gray-800 text-lg">{{ config('app.name') }}</p></div>
                <div class="text-end">
                    <p><strong>{{ __('finance.quote_number') }}:</strong> <span class="privacy-mask">{{ $quote->quote_number }}</span></p>
                    <p><strong>{{ __('finance.valid_until') }}:</strong> @if($quote->isExpired)<span class="text-red-500 font-medium"><i class="fas fa-exclamation-triangle me-1"></i>{{ $quote->valid_until?->format('Y-m-d') }}</span>@else{{ $quote->valid_until?->format('Y-m-d') ?? '-' }}@endif</p>
                </div>
            </div>
            <div class="mb-5 p-4 bg-gray-50 rounded-xl">
                <p class="text-sm font-semibold text-gray-700">{{ $quote->client?->full_name }}</p>
                <p class="text-sm text-gray-500">{{ $quote->client?->email }}</p>
                <p class="mt-2 text-xs text-gray-400 break-all">{{ route('quotes.public', $quote->public_token) }}</p>
            </div>
            <h3 class="font-semibold text-gray-700 mb-3">{{ $quote->subject }}</h3>
            <table class="w-full text-sm mb-5">
                <thead><tr class="bg-gray-50"><th class="text-start p-3 rounded-s-lg">{{ __('messages.description') }}</th><th class="text-center p-3">{{ __('finance.qty_x_price') }}</th><th class="text-end p-3 rounded-e-lg">{{ __('finance.total') }}</th></tr></thead>
                <tbody>
                @foreach($quote->items as $item)
                <tr class="border-b"><td class="p-3">{{ $item->description }}</td><td class="p-3 text-center privacy-mask">{{ $item->quantity }} × {{ number_format($item->unit_price,2) }}</td><td class="p-3 text-end font-medium privacy-mask">{{ number_format($item->total,2) }}</td></tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="2" class="p-3 text-end text-gray-500">{{ __('finance.subtotal') }}</td><td class="p-3 text-end privacy-mask">{{ number_format($quote->subtotal,2) }}</td></tr>
                    @if($quote->tax_amount > 0)<tr><td colspan="2" class="p-3 text-end text-gray-500">{{ __('finance.tax') }} ({{ $quote->tax_percentage }}%)</td><td class="p-3 text-end privacy-mask">{{ number_format($quote->tax_amount,2) }}</td></tr>@endif
                    @if($quote->discount_amount > 0)<tr><td colspan="2" class="p-3 text-end text-gray-500">{{ __('finance.discount') }}@if($quote->discount_type === 'percentage') ({{ $quote->discount_value ?? $quote->discount_percentage }}%)@endif</td><td class="p-3 text-end text-red-500 privacy-mask">-{{ number_format($quote->discount_amount,2) }}</td></tr>@endif
                    <tr class="border-t"><td colspan="2" class="p-3 text-end font-bold">{{ __('finance.total') }}</td><td class="p-3 text-end font-bold text-blue-600 text-lg privacy-mask">{{ number_format($quote->total,2) }} {{ __('messages.currency') }}</td></tr>
                </tfoot>
            </table>
            @if($quote->notes)<div class="p-4 bg-gray-50 rounded-xl text-sm text-gray-600"><p class="font-semibold mb-1">{{ __('messages.notes') }}</p>{{ $quote->notes }}</div>@endif
        </div>
    </div>
    <div class="space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-sm space-y-3">
            @php
                $sc = ['draft'=>'gray','sent'=>'blue','approved'=>'green','rejected'=>'red'][$quote->status]??'gray';
                $expired = $quote->isExpired;
            @endphp
            <div class="flex justify-between items-center"><span class="text-gray-500">{{ __('messages.status') }}</span><span class="flex items-center gap-1"><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ __('finance.quote_status.' . $quote->status) }}</span>@if($expired)<span class="px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">{{ __('finance.expired') }}</span>@endif</span></div>
            @if($expired)
            <div class="flex justify-between"><span class="text-gray-500">{{ __('finance.valid_until') }}</span><span class="text-red-500 font-medium"><i class="fas fa-exclamation-triangle me-1"></i>{{ $quote->valid_until?->format('Y-m-d') }}</span></div>
            @endif
            <div class="flex justify-between"><span class="text-gray-500">{{ __('messages.total') }}</span><span class="font-bold text-blue-600 privacy-mask">{{ number_format($quote->total,2) }} {{ __('messages.currency') }}</span></div>

            @if($quote->status === 'approved' && $quote->approved_at)
            <div class="border-t pt-3 space-y-1">
                <div class="flex justify-between"><span class="text-gray-500">{{ __('finance.date') }}</span><span class="text-gray-700">{{ $quote->approved_at->format('Y-m-d H:i') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">{{ __('messages.by') }}</span><span class="text-gray-700">@if($quote->approvedBy){{ __('finance.approved_by_admin', ['name' => $quote->approvedBy->name]) }}@else{{ $quote->client?->email }} <span class="text-gray-400 text-xs">({{ $quote->approved_by_ip }})</span>@endif</span></div>
            </div>
            @endif

            @if($quote->status === 'rejected' && $quote->rejection_reason)
            <div class="border-t pt-3">
                <p class="text-xs font-semibold text-red-600 mb-1">{{ __('finance.modification_reason') }}</p>
                <p class="text-xs text-gray-600 whitespace-pre-line">{{ $quote->rejection_reason }}</p>
            </div>
            <form action="{{ route('quotes.redraft', $quote) }}" method="POST">
                @csrf
                <button type="submit" class="block w-full bg-amber-500 hover:bg-amber-600 text-white text-center py-2 rounded-xl text-sm transition mt-2"><i class="fas fa-undo me-1"></i>{{ __('finance.return_to_draft') }}</button>
            </form>
            @endif

            @if($quote->status === 'approved' && !$quote->converted_to_project_id)
            <div class="border-t pt-3">
                <p class="text-gray-500 text-xs mb-2">{{ __('finance.convert_quote_to_project_label') }}</p>
                <a href="{{ route('projects.create') }}?quote_id={{ $quote->id }}&client_id={{ $quote->client_id }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 rounded-xl text-sm transition">{{ __('finance.convert_to_project') }}</a>
            </div>
            @endif
            @if($quote->status === 'approved' && !$quote->invoice)
            <div class="border-t pt-3">
                <p class="text-gray-500 text-xs mb-2">{{ __('finance.convert_quote_to_invoice_label') }}</p>
                <form action="{{ route('quotes.convert-to-invoice', $quote) }}" method="POST">
                    @csrf
                    <button type="submit" class="block w-full bg-purple-600 hover:bg-purple-700 text-white text-center py-2 rounded-xl text-sm transition">{{ __('finance.create_invoice') }}</button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

@if(isset($recentViews))
<div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="fas fa-eye text-indigo-500"></i>
        {{ __('finance.quote_views') }} ({{ $quote->views()->count() }})
    </h3>
    @if($recentViews->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-start text-gray-500 border-b">
                        <th class="text-start py-2 px-2">{{ __('finance.viewed_at') }}</th>
                        <th class="text-start py-2 px-2">{{ __('finance.viewer_ip') }}</th>
                        <th class="text-start py-2 px-2 hidden md:table-cell">{{ __('finance.user_agent') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentViews as $view)
                    <tr class="border-b last:border-b-0 hover:bg-gray-50">
                        <td class="py-2 px-2 text-gray-700">{{ $view->viewed_at?->format('Y-m-d H:i') }}</td>
                        <td class="py-2 px-2 text-gray-600 font-mono text-xs">{{ $view->ip_address ?? '-' }}</td>
                        <td class="py-2 px-2 text-gray-400 text-xs hidden md:table-cell truncate max-w-md">{{ \Illuminate\Support\Str::limit($view->user_agent, 80) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-400 text-sm text-center py-4">{{ __('finance.no_views') }}</p>
    @endif
</div>
@endif
@endsection
