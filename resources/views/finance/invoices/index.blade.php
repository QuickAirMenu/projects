@extends('layouts.app')
@section('title', __('finance.invoices'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('finance.invoices') }}</h1>
    <a href="{{ route('invoices.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm transition font-medium">
        <i class="fas fa-plus me-2"></i>{{ __('finance.add_invoice') }}
    </a>
</div>

<div class="flex gap-2 mb-4">
    <a href="{{ route('invoices.index') }}" 
       class="px-4 py-2 rounded-xl text-sm font-medium transition {{ !$trashed ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        <i class="fas fa-file-alt me-1"></i>الكل
    </a>
    <a href="{{ route('invoices.index', ['trashed' => 1]) }}" 
       class="px-4 py-2 rounded-xl text-sm font-medium transition {{ $trashed ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        <i class="fas fa-archive me-1"></i>الأرشيف
    </a>
</div>

{{-- Financial Summary --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => 'إجمالي الفواتير', 'val' => number_format($stats['total_invoiced'],2), 'color' => 'blue',  'icon' => 'fa-file-invoice-dollar'],
        ['label' => 'المحصّل',         'val' => number_format($stats['total_paid'],2),     'color' => 'green', 'icon' => 'fa-money-bill-wave'],
        ['label' => 'غير مدفوع',       'val' => number_format($stats['unpaid'],2),         'color' => 'red',   'icon' => 'fa-exclamation-circle'],
        ['label' => 'متأخرة (عدد)',    'val' => $stats['overdue'],                         'color' => 'orange','icon' => 'fa-clock'],
    ] as $c)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-{{ $c['color'] }}-100 flex items-center justify-center">
            <i class="fas {{ $c['icon'] }} text-{{ $c['color'] }}-600 text-lg"></i>
        </div>
        <div class="min-w-0">
            <p class="text-lg font-bold text-gray-800 truncate">{{ $c['val'] }}</p>
            <p class="text-xs text-gray-500">{{ $c['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <select name="payment_status" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('finance.status_unpaid') }}</option>
            @foreach(['unpaid','partially_paid','paid','overdue'] as $s)
                <option value="{{ $s }}" {{ request('payment_status') === $s ? 'selected' : '' }}>{{ __('finance.status_'.$s) }}</option>
            @endforeach
        </select>
        <select name="invoice_type" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">نوع الفاتورة</option>
            @foreach(['initial','partial','final','additional'] as $t)
                <option value="{{ $t }}" {{ request('invoice_type') === $t ? 'selected' : '' }}>{{ __('finance.type_'.$t) }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm transition">
            <i class="fas fa-search me-1"></i>{{ __('messages.filter') }}
        </button>
        <a href="{{ route('invoices.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-xl text-sm transition">{{ __('messages.reset') }}</a>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('finance.invoice_number') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">{{ __('client.client') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('project.project') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('finance.total') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">{{ __('finance.remaining') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">{{ __('messages.status') }}</th>
                <th class="text-end px-5 py-3 font-semibold text-gray-600">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($invoices as $invoice)
            @php $sc = ['paid'=>'green','partially_paid'=>'yellow','overdue'=>'red','unpaid'=>'gray'][$invoice->payment_status] ?? 'gray' @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3">
                    <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-gray-800 hover:text-blue-600">{{ $invoice->invoice_number }}</a>
                    <p class="text-xs text-gray-400">{{ $invoice->invoice_date->format('Y-m-d') }}</p>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $invoice->client?->name }}</td>
                <td class="px-4 py-3 hidden lg:table-cell text-gray-600">{{ $invoice->project?->name ?? '-' }}</td>
                <td class="px-4 py-3 hidden lg:table-cell font-medium">{{ number_format($invoice->total, 2) }}</td>
                <td class="px-4 py-3 hidden xl:table-cell font-medium text-red-600">{{ number_format($invoice->remaining_amount, 2) }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ __('finance.status_'.$invoice->payment_status) }}</span>
                </td>
                <td class="px-5 py-3 text-end">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('invoices.show', $invoice) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"><i class="fas fa-eye"></i></a>
                        @if($trashed)
                        <form action="{{ route('invoices.restore', $invoice) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg" title="استعادة">
                                <i class="fas fa-undo"></i>
                            </button>
                        </form>
                        @else
                        <a href="{{ route('invoices.edit', $invoice) }}" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirmDelete(this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="py-16 text-center text-gray-400">
                <i class="fas fa-file-invoice text-5xl mb-3 block text-gray-200"></i>
                لا توجد فواتير
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($invoices->hasPages())
    <div class="px-5 py-4 border-t">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
