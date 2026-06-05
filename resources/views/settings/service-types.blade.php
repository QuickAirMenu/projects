@extends('layouts.app')
@section('title', 'أنواع الخدمات')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">أنواع الخدمات</h1>
    <a href="{{ route('settings.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>
@include('layouts.components.alert')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Add Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">إضافة نوع خدمة</h3>
        <form action="{{ route('settings.service-types.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">الاسم (AR) *</label>
                <input type="text" name="name_ar" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">الاسم (EN) *</label>
                <input type="text" name="name_en" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">الأيقونة (Font Awesome)</label>
                <input type="text" name="icon" placeholder="fa-laptop" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">اللون</label>
                <input type="color" name="color" value="#3B82F6" class="w-full h-10 border border-gray-300 rounded-xl cursor-pointer">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl text-sm transition">
                <i class="fas fa-plus me-1"></i>{{ __('messages.add') }}
            </button>
        </form>
    </div>
    {{-- List --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 border-b">
                <th class="text-start px-5 py-3 font-semibold text-gray-600">اسم الخدمة</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">اللون</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">الحالة</th>
                <th class="text-end px-5 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($serviceTypes as $type)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            @if($type->icon)<i class="fas {{ $type->icon }}" style="color:{{ $type->color }}"></i>@endif
                            <span class="font-medium text-gray-800">{{ $type->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3"><div class="w-6 h-6 rounded-lg" style="background:{{ $type->color }}"></div></td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $type->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $type->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-end">
                        <form action="{{ route('settings.service-types.destroy', $type) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 text-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-10 text-center text-gray-400">لا توجد بيانات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
