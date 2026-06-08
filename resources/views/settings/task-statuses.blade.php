@extends('layouts.app')
@section('title', 'حالات المهام')
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">حالات المهام</h1>
    <a href="{{ route('settings.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">إضافة حالة جديدة</h3>
        <form action="{{ route('settings.task-statuses.store') }}" method="POST" class="space-y-3">
            @csrf
            <div><label class="block text-xs font-medium text-gray-700 mb-1">الاسم (AR) *</label>
                <input type="text" name="name_ar" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-xs font-medium text-gray-700 mb-1">الاسم (EN) *</label>
                <input type="text" name="name_en" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-xs font-medium text-gray-700 mb-1">Slug *</label>
                <input type="text" name="slug" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-xs font-medium text-gray-700 mb-1">اللون</label>
                <input type="color" name="color" value="#3B82F6" class="w-full h-10 border border-gray-300 rounded-xl cursor-pointer"></div>
            <div><label class="block text-xs font-medium text-gray-700 mb-1">الترتيب</label>
                <input type="number" name="sort_order" value="{{ $statuses->count() + 1 }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl text-sm transition">
                <i class="fas fa-plus me-1"></i>{{ __('messages.add') }}
            </button>
        </form>
    </div>
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 border-b">
                <th class="text-start px-5 py-3 font-semibold text-gray-600">الحالة</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">Slug</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">اللون</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">الترتيب</th>
                <th class="text-end px-5 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($statuses as $status)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background:{{ $status->color }}"></div>
                            <span class="font-medium text-gray-800">{{ $status->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $status->slug }}</td>
                    <td class="px-4 py-3"><div class="w-6 h-6 rounded-lg" style="background:{{ $status->color }}"></div></td>
                    <td class="px-4 py-3 text-gray-500">{{ $status->sort_order }}</td>
                    <td class="px-5 py-3 text-end">
                        <form action="{{ route('settings.task-statuses.destroy', $status) }}" method="POST" onsubmit="return confirmDelete(this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 text-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-10 text-center text-gray-400">لا توجد بيانات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
