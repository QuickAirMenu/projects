@extends('layouts.app')
@section('title', __('finance.contracts'))
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('finance.contracts') }}</h1>
    <a href="{{ route('contracts.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm transition font-medium">
        <i class="fas fa-plus me-2"></i>{{ __('finance.add_contract') }}
    </a>
</div>

<div class="flex gap-2 mb-4">
    <a href="{{ route('contracts.index') }}" 
       class="px-4 py-2 rounded-xl text-sm font-medium transition {{ !$trashed ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        <i class="fas fa-file-alt me-1"></i>الكل
    </a>
    <a href="{{ route('contracts.index', ['trashed' => 1]) }}" 
       class="px-4 py-2 rounded-xl text-sm font-medium transition {{ $trashed ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        <i class="fas fa-archive me-1"></i>الأرشيف
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    @foreach([
        ['label' => 'إجمالي قيمة العقود النشطة', 'val' => number_format($stats['active_value'], 2) . ' ' . __('messages.currency'), 'meta' => 'العقود الجارية فقط', 'icon' => 'fa-file-contract', 'chip' => 'bg-blue-50 text-blue-600'],
        ['label' => 'عدد العقود المنتهية', 'val' => $stats['ended_count'], 'meta' => 'مكتملة أو موقوفة', 'icon' => 'fa-flag-checkered', 'chip' => 'bg-amber-50 text-amber-600'],
        ['label' => 'موقعة من العميل', 'val' => $stats['signed_count'], 'meta' => 'جاهزة للمتابعة', 'icon' => 'fa-pen-fancy', 'chip' => 'bg-emerald-50 text-emerald-600'],
    ] as $card)
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $card['label'] }}</p>
                <p class="mt-2 text-xl font-bold text-gray-900 break-words">{{ $card['val'] }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ $card['meta'] }}</p>
            </div>
            <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $card['chip'] }}">
                <i class="fas {{ $card['icon'] }}"></i>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 border-b border-gray-100">
            <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('finance.contract_number') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">{{ __('project.project') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('finance.contract_value') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600">الحالة</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">تاريخ البداية</th>
            <th class="text-end px-5 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($contracts as $contract)
            @php $statusClasses = ['draft' => 'bg-gray-100 text-gray-700', 'active' => 'bg-green-100 text-green-700', 'completed' => 'bg-blue-100 text-blue-700', 'terminated' => 'bg-red-100 text-red-700'][$contract->status] ?? 'bg-gray-100 text-gray-700'; @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3 font-medium text-gray-800">
                    <a href="{{ route('contracts.show', $contract) }}" class="hover:text-blue-600">{{ $contract->contract_number }}</a>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $contract->project?->name }}</td>
                <td class="px-4 py-3 hidden lg:table-cell font-medium">{{ number_format($contract->contract_value,2) }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusClasses }}">{{ ucfirst($contract->status) }}</span>
                </td>
                <td class="px-4 py-3 hidden xl:table-cell text-gray-500">{{ \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') }}</td>
                <td class="px-5 py-3 text-end">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('contracts.show', $contract) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"><i class="fas fa-eye"></i></a>
                        @if($trashed)
                        <form action="{{ route('contracts.restore', $contract) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg" title="استعادة">
                                <i class="fas fa-undo"></i>
                            </button>
                        </form>
                        @else
                        <a href="{{ route('contracts.edit', $contract) }}" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('contracts.destroy', $contract) }}" method="POST" onsubmit="return confirmDelete(this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-16 text-center text-gray-400"><i class="fas fa-file-contract text-5xl mb-3 block text-gray-200"></i>لا توجد عقود</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($contracts->hasPages())
    <div class="px-5 py-4 border-t">{{ $contracts->links() }}</div>
    @endif
</div>
@endsection
