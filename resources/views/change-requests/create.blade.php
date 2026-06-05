@extends('layouts.app')
@section('title', 'طلب تعديل جديد')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">طلب تعديل جديد</h1>
    <a href="{{ route('change-requests.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

@include('layouts.components.alert')

<div class="max-w-3xl bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <form action="{{ route('change-requests.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">عنوان الطلب *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">المشروع *</label>
                <select name="project_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- اختر --</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">مصدر الطلب *</label>
                <select name="requested_by" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="client" {{ old('requested_by') === 'client' ? 'selected' : '' }}>من العميل</option>
                    <option value="internal" {{ old('requested_by') === 'internal' ? 'selected' : '' }}>داخلي</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">النطاق *</label>
                <select name="scope_type" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="in_scope" {{ old('scope_type', 'in_scope') === 'in_scope' ? 'selected' : '' }}>ضمن النطاق</option>
                    <option value="out_of_scope" {{ old('scope_type') === 'out_of_scope' ? 'selected' : '' }}>خارج النطاق — يحتاج تسعير إضافي</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.priority') }} *</label>
                <select name="priority" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['low'=>'منخفض','medium'=>'متوسط','high'=>'عالي'] as $val => $label)
                        <option value="{{ $val }}" {{ old('priority','medium') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">التكلفة الإضافية (ر.س)</label>
                <input type="number" name="additional_cost" value="{{ old('additional_cost', 0) }}" step="0.01" min="0"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الساعات الإضافية</label>
                <input type="number" name="additional_hours" value="{{ old('additional_hours', 0) }}" step="0.5" min="0"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">وصف الطلب *</label>
                <textarea name="description" rows="4" required
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">تحليل التأثير</label>
                <textarea name="impact_analysis" rows="3"
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('impact_analysis') }}</textarea>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
                <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
            </button>
            <a href="{{ route('change-requests.index') }}" class="bg-gray-100 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
