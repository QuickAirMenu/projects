@extends('layouts.app')
@section('title', __('finance.contracts'))
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('finance.contracts') }}</h1>
    <a href="{{ route('contracts.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm transition font-medium">
        <i class="fas fa-plus me-2"></i>{{ __('finance.add_contract') }}
    </a>
</div>
@include('layouts.components.alert')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 border-b">
            <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('finance.contract_number') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">{{ __('project.project') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('finance.contract_value') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600">الحالة</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">تاريخ البداية</th>
            <th class="text-end px-5 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($contracts as $contract)
            @php $sc = ['draft'=>'gray','active'=>'green','completed'=>'blue','terminated'=>'red'][$contract->status]??'gray'; @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-medium text-gray-800">{{ $contract->contract_number }}</td>
                <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $contract->project?->name }}</td>
                <td class="px-4 py-3 hidden lg:table-cell font-medium">{{ number_format($contract->contract_value,2) }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ ucfirst($contract->status) }}</span>
                </td>
                <td class="px-4 py-3 hidden xl:table-cell text-gray-500">{{ \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') }}</td>
                <td class="px-5 py-3 text-end">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('contracts.show', $contract) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('contracts.edit', $contract) }}" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('contracts.destroy', $contract) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg"><i class="fas fa-trash"></i></button>
                        </form>
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
