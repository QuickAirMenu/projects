@extends('layouts.app')
@section('title', $quote->quote_number)
@section('content')
<div class="mb-5 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $quote->quote_number }}</h1>
        <p class="text-gray-500 text-sm">{{ $quote->client?->name }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('quotes.pdf', $quote) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-file-pdf me-2"></i>تحميل PDF</a>
        <a href="{{ route('quotes.public', $quote->public_token) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-link me-2"></i>الرابط العام</a>
        @if($quote->status === 'draft')
        <a href="{{ route('quotes.edit', $quote) }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-edit me-2"></i>تعديل</a>
        <form action="{{ route('quotes.send', $quote) }}" method="POST">
            @csrf
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-paper-plane me-2"></i>إرسال</button>
        </form>
        @endif
        @if($whatsappUrl)
        <a href="{{ $whatsappUrl }}" target="_blank" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-xl text-sm transition"><i class="fab fa-whatsapp me-2"></i>إرسال عبر WhatsApp</a>
        @endif
        <a href="{{ route('quotes.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}</a>
    </div>
</div>
@include('layouts.components.alert')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between mb-6 text-sm">
                <div><p class="font-bold text-gray-800 text-lg">{{ config('app.name') }}</p></div>
                <div class="text-end">
                    <p><strong>{{ __('finance.quote_number') }}:</strong> {{ $quote->quote_number }}</p>
                    <p><strong>{{ __('finance.valid_until') }}:</strong> {{ $quote->valid_until?->format('Y-m-d') ?? '-' }}</p>
                </div>
            </div>
            <div class="mb-5 p-4 bg-gray-50 rounded-xl">
                <p class="text-sm font-semibold text-gray-700">{{ $quote->client?->name }}</p>
                <p class="text-sm text-gray-500">{{ $quote->client?->email }}</p>
                <p class="mt-2 text-xs text-gray-400 break-all">{{ route('quotes.public', $quote->public_token) }}</p>
            </div>
            <h3 class="font-semibold text-gray-700 mb-3">{{ $quote->subject }}</h3>
            <table class="w-full text-sm mb-5">
                <thead><tr class="bg-gray-50"><th class="text-start p-3 rounded-s-lg">{{ __('messages.description') }}</th><th class="text-center p-3">الكمية × السعر</th><th class="text-end p-3 rounded-e-lg">{{ __('finance.total') }}</th></tr></thead>
                <tbody>
                @foreach($quote->items as $item)
                <tr class="border-b"><td class="p-3">{{ $item->description }}</td><td class="p-3 text-center">{{ $item->quantity }} × {{ number_format($item->unit_price,2) }}</td><td class="p-3 text-end font-medium">{{ number_format($item->total,2) }}</td></tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="2" class="p-3 text-end text-gray-500">{{ __('finance.subtotal') }}</td><td class="p-3 text-end">{{ number_format($quote->subtotal,2) }}</td></tr>
                    @if($quote->tax_amount > 0)<tr><td colspan="2" class="p-3 text-end text-gray-500">{{ __('finance.tax') }} ({{ $quote->tax_percentage }}%)</td><td class="p-3 text-end">{{ number_format($quote->tax_amount,2) }}</td></tr>@endif
                    @if($quote->discount_amount > 0)<tr><td colspan="2" class="p-3 text-end text-gray-500">{{ __('finance.discount') }}</td><td class="p-3 text-end text-red-500">-{{ number_format($quote->discount_amount,2) }}</td></tr>@endif
                    <tr class="border-t"><td colspan="2" class="p-3 text-end font-bold">{{ __('finance.total') }}</td><td class="p-3 text-end font-bold text-blue-600 text-lg">{{ number_format($quote->total,2) }} ر.س</td></tr>
                </tfoot>
            </table>
            @if($quote->notes)<div class="p-4 bg-gray-50 rounded-xl text-sm text-gray-600"><p class="font-semibold mb-1">{{ __('messages.notes') }}</p>{{ $quote->notes }}</div>@endif
        </div>
    </div>
    <div class="space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-sm space-y-3">
            @php $sc = ['draft'=>'gray','sent'=>'blue','approved'=>'green','rejected'=>'red'][$quote->status]??'gray' @endphp
            <div class="flex justify-between"><span class="text-gray-500">الحالة</span><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ ucfirst($quote->status) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">الإجمالي</span><span class="font-bold text-blue-600">{{ number_format($quote->total,2) }} ر.س</span></div>
            @if($quote->status === 'approved' && !$quote->converted_to_project_id)
            <div class="border-t pt-3">
                <p class="text-gray-500 text-xs mb-2">تحويل العرض إلى مشروع</p>
                <a href="{{ route('projects.create') }}?quote_id={{ $quote->id }}&client_id={{ $quote->client_id }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 rounded-xl text-sm transition">{{ __('finance.convert_to_project') }}</a>
            </div>
            @endif
            @if($quote->status === 'approved' && !$quote->invoice)
            <div class="border-t pt-3">
                <p class="text-gray-500 text-xs mb-2">تحويل العرض إلى فاتورة</p>
                <form action="{{ route('quotes.convert-to-invoice', $quote) }}" method="POST">
                    @csrf
                    <button type="submit" class="block w-full bg-purple-600 hover:bg-purple-700 text-white text-center py-2 rounded-xl text-sm transition">إنشاء فاتورة</button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
