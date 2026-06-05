@extends('layouts.app')
@section('title', __('finance.add_contract'))
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ isset($contract) ? 'تعديل العقد' : __('finance.add_contract') }}</h1>
    <a href="{{ route('contracts.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}</a>
</div>
@include('layouts.components.alert')
<div class="max-w-3xl bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
<form action="{{ isset($contract) ? route('contracts.update', $contract) : route('contracts.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($contract)) @method('PUT') @endif
    @if(!empty($prefillData['invoice']))
        <div class="mb-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
            تم استيراد بيانات الفاتورة {{ $prefillData['invoice']->invoice_number }}
        </div>
        <input type="hidden" name="invoice_id" value="{{ $prefillData['invoice']->id }}">
    @endif
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.project') }} *</label>
            <select name="project_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- اختر --</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ old('project_id', $contract->project_id ?? $prefillData['project_id'] ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.client') }} *</label>
            <select name="client_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- اختر --</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ old('client_id', $contract->client_id ?? $prefillData['client_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">عنوان العقد *</label>
            <input type="text" name="title" value="{{ old('title', $contract->title ?? $prefillData['title'] ?? '') }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">الوصف</label>
            <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $contract->description ?? '') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">المخرجات</label>
            <textarea name="deliverables" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="المخرجات">{{ old('deliverables', $contract->deliverables ?? '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">قيمة العقد *</label>
            <input type="number" name="contract_value" value="{{ old('contract_value', $contract->contract_value ?? $prefillData['contract_value'] ?? '') }}" step="0.01" min="0" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الحالة *</label>
            <select name="status" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach(['draft'=>'مسودة','active'=>'نشط','completed'=>'مكتمل','terminated'=>'منتهي'] as $v=>$l)
                    <option value="{{ $v }}" {{ old('status', $contract->status??'draft') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ البداية *</label>
            <input type="date" name="start_date" value="{{ old('start_date', isset($contract) ? \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') : now()->toDateString()) }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الانتهاء</label>
            <input type="date" name="end_date" value="{{ old('end_date', isset($contract) && $contract->end_date ? \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d') : '') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">شروط الدفع</label>
            <textarea name="payment_terms" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('payment_terms', $contract->payment_terms??'') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">الشروط والأحكام</label>
            <textarea name="terms_and_conditions" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('terms_and_conditions', $contract->terms_and_conditions ?? '') }}</textarea>
        </div>
        <div class="md:col-span-2 flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4">
                <input type="checkbox" name="signed_by_client" id="signed_by_client" value="1" {{ old('signed_by_client', isset($contract) ? $contract->signed_by_client : false) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <div class="flex-1">
                    <label for="signed_by_client" class="block text-sm font-medium text-gray-700">موقّع من العميل</label>
                    <p class="text-xs text-gray-500 mt-1">فعّل هذا الخيار إذا تم توقيع العقد.</p>
                <div id="signed_at_wrap" class="mt-3 {{ old('signed_by_client', isset($contract) ? $contract->signed_by_client : false) ? '' : 'hidden' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ التوقيع</label>
                    <input type="date" name="signed_at" value="{{ old('signed_at', isset($contract->signed_at) && $contract->signed_at ? $contract->signed_at->format('Y-m-d') : '') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">ملف العقد (PDF)</label>
            <input type="file" name="contract_file" accept=".pdf,.doc,.docx" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm">
        </div>
    </div>
    <div class="flex gap-3 mt-6">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl transition"><i class="fas fa-save me-2"></i>{{ __('messages.save') }}</button>
        <a href="{{ route('contracts.index') }}" class="bg-gray-100 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition">{{ __('messages.cancel') }}</a>
    </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const checkbox = document.getElementById('signed_by_client');
    const wrap = document.getElementById('signed_at_wrap');
    if (!checkbox || !wrap) return;

    const sync = () => wrap.classList.toggle('hidden', !checkbox.checked);
    checkbox.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
