@extends('layouts.app')
@section('title', 'طلبات التعديل')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">طلبات التعديل</h1>
    <a href="{{ route('change-requests.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm transition font-medium">
        <i class="fas fa-plus me-2"></i>طلب تعديل جديد
    </a>
</div>


{{-- Filters --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <select name="status" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">الحالة</option>
            @foreach(['pending'=>'قيد الانتظار','approved'=>'معتمد','rejected'=>'مرفوض','implemented'=>'منفّذ'] as $val => $label)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="scope_type" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">النطاق</option>
            <option value="in_scope" {{ request('scope_type') === 'in_scope' ? 'selected' : '' }}>ضمن النطاق</option>
            <option value="out_of_scope" {{ request('scope_type') === 'out_of_scope' ? 'selected' : '' }}>خارج النطاق</option>
        </select>
        <select name="project_id" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">المشروع</option>
            @foreach($projects as $p)
                <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm transition">
            <i class="fas fa-search me-1"></i>{{ __('messages.filter') }}
        </button>
        <a href="{{ route('change-requests.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-xl text-sm transition">{{ __('messages.reset') }}</a>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-start px-5 py-3 font-semibold text-gray-600">الطلب</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">المشروع</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">النطاق</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">الحالة</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">التكلفة الإضافية</th>
                <th class="text-end px-5 py-3 font-semibold text-gray-600">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($changeRequests as $cr)
            @php
                $statusColors = ['pending'=>'yellow','approved'=>'green','rejected'=>'red','implemented'=>'blue'];
                $sc = $statusColors[$cr->status] ?? 'gray';
                $statusLabels = ['pending'=>'قيد الانتظار','approved'=>'معتمد','rejected'=>'مرفوض','implemented'=>'منفّذ'];
            @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3">
                    <a href="{{ route('change-requests.show', $cr) }}" class="font-medium text-gray-800 hover:text-blue-600">{{ $cr->title }}</a>
                    <p class="text-xs text-gray-400">{{ $cr->request_number }} · {{ $cr->requested_by === 'client' ? 'العميل' : 'داخلي' }}</p>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $cr->project?->name }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $cr->scope_type === 'in_scope' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                        {{ $cr->scope_type === 'in_scope' ? 'ضمن النطاق' : 'خارج النطاق' }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">
                        {{ $statusLabels[$cr->status] ?? $cr->status }}
                    </span>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell text-gray-600">
                    {{ $cr->additional_cost > 0 ? number_format($cr->additional_cost, 2) . ' ر.س' : '-' }}
                </td>
                <td class="px-5 py-3 text-end">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('change-requests.show', $cr) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('change-requests.edit', $cr) }}" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg"><i class="fas fa-edit"></i></a>
                        @if($cr->status === 'pending')
                        <form action="{{ route('change-requests.approve', $cr) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-1.5 text-green-500 hover:bg-green-50 rounded-lg" title="اعتماد"><i class="fas fa-check"></i></button>
                        </form>
                        @endif
                        <form action="{{ route('change-requests.destroy', $cr) }}" method="POST" onsubmit="return confirmDelete(this)" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-16 text-center text-gray-400">
                <i class="fas fa-edit text-5xl mb-3 block text-gray-200"></i>
                لا توجد طلبات تعديل
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($changeRequests->hasPages())
    <div class="px-5 py-4 border-t">{{ $changeRequests->links() }}</div>
    @endif
</div>
@endsection
