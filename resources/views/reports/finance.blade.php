@extends('layouts.app')
@section('title', __('messages.finance_report'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.finance_report') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ __('messages.invoices_payments') }}</p>
    </div>
    <a href="{{ route('reports.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

{{-- Financial KPIs --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => __('finance.total_invoiced'), 'val' => number_format($stats['total_invoiced'],2).' '. __('messages.currency'), 'color' => 'blue', 'icon' => 'fa-file-invoice-dollar'],
        ['label' => __('messages.collected_label'),          'val' => number_format($stats['total_paid'],2).' '. __('messages.currency'),    'color' => 'green','icon' => 'fa-money-bill-wave'],
        ['label' => __('messages.unpaid_label'),        'val' => number_format($stats['total_unpaid'],2).' '. __('messages.currency'),  'color' => 'red',  'icon' => 'fa-exclamation'],
        ['label' => __('messages.overdue_label'),           'val' => $stats['overdue_count'],                          'color' => 'orange','icon' => 'fa-clock'],
    ] as $c)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="w-10 h-10 rounded-xl bg-{{ $c['color'] }}-100 flex items-center justify-center mb-3">
            <i class="fas {{ $c['icon'] }} text-{{ $c['color'] }}-600"></i>
        </div>
        <p class="text-lg font-bold text-gray-800">{{ $c['val'] }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $c['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Monthly Chart Data --}}
@if($monthlyData->count())
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
    <h3 class="font-semibold text-gray-700 mb-4">{{ __('messages.monthly_performance') }} {{ now()->year }}</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b">
                <th class="text-start py-2 text-gray-500">{{ __('messages.month') }}</th>
                <th class="text-end py-2 text-gray-500">{{ __('finance.total_invoiced') }}</th>
                <th class="text-end py-2 text-gray-500">{{ __('messages.collected_label') }}</th>
                <th class="text-end py-2 text-gray-500">{{ __('messages.collection_rate') }}</th>
            </tr></thead>
            <tbody>
                @foreach($monthlyData as $row)
                @php $months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر']; @endphp
                <tr class="border-b last:border-0 hover:bg-gray-50">
                    <td class="py-2 text-gray-700">{{ $months[$row->month-1] ?? $row->month }}</td>
                    <td class="py-2 text-end text-gray-800">{{ number_format($row->total,2) }}</td>
                    <td class="py-2 text-end text-green-600">{{ number_format($row->paid,2) }}</td>
                    <td class="py-2 text-end">
                        @php $pct = $row->total > 0 ? round($row->paid/$row->total*100) : 0 @endphp
                        <div class="flex items-center justify-end gap-2">
                            <div class="w-16 bg-gray-200 rounded-full h-1.5"><div class="bg-green-500 h-1.5 rounded-full" style="width:{{ $pct }}%"></div></div>
                            <span class="text-xs text-gray-500">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Invoices Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-3">
        <select name="payment_status" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('finance.payment_status') }}</option>
            @foreach(['unpaid'=>'unpaid','partially_paid'=>'partially_paid','paid'=>'paid','overdue'=>'overdue'] as $v=>$l)
                <option value="{{ $v }}" {{ request('payment_status') === $v ? 'selected' : '' }}>{{ __('finance.status_'.$v) }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm transition"><i class="fas fa-search me-1"></i>{{ __('messages.filter') }}</button>
        <a href="{{ route('reports.finance') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-xl text-sm transition">{{ __('messages.reset') }}</a>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 border-b">
            <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('finance.invoice') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">{{ __('client.client') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('messages.total') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('finance.paid_amount') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">{{ __('finance.remaining') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600">{{ __('messages.status') }}</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($invoices as $inv)
            @php $sc = ['paid'=>'green','partially_paid'=>'yellow','overdue'=>'red','unpaid'=>'gray'][$inv->payment_status]??'gray'; @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3">
                    <a href="{{ route('invoices.show', $inv) }}" class="font-medium text-gray-800 hover:text-blue-600">{{ $inv->invoice_number }}</a>
                    <p class="text-xs text-gray-400">{{ $inv->invoice_date->format('Y-m-d') }}</p>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $inv->client?->name }}</td>
                <td class="px-4 py-3 hidden lg:table-cell font-medium">{{ number_format($inv->total,2) }}</td>
                <td class="px-4 py-3 hidden lg:table-cell text-green-600">{{ number_format($inv->paid_amount,2) }}</td>
                <td class="px-4 py-3 hidden xl:table-cell text-red-500">{{ number_format($inv->remaining_amount,2) }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ __('finance.status_'.$inv->payment_status) }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-12 text-center text-gray-400">{{ __('messages.no_invoices') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($invoices->hasPages())
    <div class="px-5 py-4 border-t">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
