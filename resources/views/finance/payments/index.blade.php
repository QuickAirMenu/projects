@extends('layouts.app')
@section('title', __('finance.payments'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('finance.payments') }}</h1>
        <p class="text-gray-500 text-sm mt-1">إجمالي المحصّل: <strong>{{ number_format($stats['total_received'], 2) }} ر.س</strong></p>
    </div>
</div>

<div class="flex gap-2 mb-4">
    <a href="{{ route('payments.index') }}" 
       class="px-4 py-2 rounded-xl text-sm font-medium transition {{ !$trashed ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        <i class="fas fa-file-alt me-1"></i>الكل
    </a>
    <a href="{{ route('payments.index', ['trashed' => 1]) }}" 
       class="px-4 py-2 rounded-xl text-sm font-medium transition {{ $trashed ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        <i class="fas fa-archive me-1"></i>الأرشيف
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
            <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-xl font-bold text-gray-800">{{ number_format($stats['total_received'], 2) }}</p>
            <p class="text-xs text-gray-500">إجمالي المحصّل</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
            <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
        </div>
        <div>
            <p class="text-xl font-bold text-gray-800">{{ number_format($stats['this_month'], 2) }}</p>
            <p class="text-xs text-gray-500">هذا الشهر</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
            <i class="fas fa-receipt text-purple-600 text-xl"></i>
        </div>
        <div>
            <p class="text-xl font-bold text-gray-800">{{ $stats['count'] }}</p>
            <p class="text-xs text-gray-500">عدد الدفعات</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b">
                <th class="text-start px-5 py-3 font-semibold text-gray-600">رقم الدفعة</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">الفاتورة</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">العميل</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">المبلغ</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">طريقة الدفع</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">التاريخ</th>
                <th class="text-end px-5 py-3 font-semibold text-gray-600">الإجراءات</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($payments as $payment)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3 font-medium text-gray-800">{{ $payment->payment_number }}</td>
                <td class="px-4 py-3 hidden md:table-cell">
                    <a href="{{ route('invoices.show', $payment->invoice_id) }}" class="text-blue-600 hover:underline">{{ $payment->invoice?->invoice_number }}</a>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell text-gray-600">{{ $payment->invoice?->client?->name }}</td>
                <td class="px-4 py-3 font-bold text-green-600">{{ number_format($payment->amount, 2) }} ر.س</td>
                <td class="px-4 py-3 hidden xl:table-cell text-gray-500">{{ __('finance.method_'.$payment->payment_method) }}</td>
                <td class="px-4 py-3 hidden xl:table-cell text-gray-500">{{ $payment->payment_date->format('Y-m-d') }}</td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-end gap-2">
                        @if($trashed)
                        <form action="{{ route('payments.restore', $payment) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 text-xs px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition" title="استعادة">
                                <i class="fas fa-undo"></i>
                                استعادة
                            </button>
                        </form>
                        @else
                        <a href="{{ route('payments.edit', $payment) }}" class="inline-flex items-center gap-1 text-xs px-3 py-2 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                            <i class="fas fa-pen"></i>
                            {{ __('messages.edit') ?? 'تعديل' }}
                        </a>
                        <form action="{{ route('payments.destroy', $payment) }}" method="POST" onsubmit="return confirmDelete(this)">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1 text-xs px-3 py-2 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition">
                                <i class="fas fa-trash"></i>
                                {{ __('messages.delete') ?? 'حذف' }}
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="py-16 text-center text-gray-400">
                <i class="fas fa-money-bill text-5xl mb-3 block text-gray-200"></i>
                لا توجد دفعات مسجلة
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($payments->hasPages())
    <div class="px-5 py-4 border-t">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
