@extends('layouts.app')
@section('title', $contract->contract_number)
@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $contract->title }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $contract->contract_number }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('contracts.edit', $contract) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-edit me-2"></i>تعديل</a>
        <a href="{{ route('contracts.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}</a>
    </div>
</div>
@include('layouts.components.alert')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-2 gap-5 text-sm mb-5">
            <div><p class="text-gray-500 mb-1">المشروع</p><a href="{{ route('projects.show', $contract->project) }}" class="font-medium text-blue-600 hover:underline">{{ $contract->project?->name }}</a></div>
            <div><p class="text-gray-500 mb-1">العميل</p><p class="font-medium">{{ $contract->client?->name }}</p></div>
            <div><p class="text-gray-500 mb-1">قيمة العقد</p><p class="font-bold text-xl text-gray-800">{{ number_format($contract->contract_value,2) }} ر.س</p></div>
            <div><p class="text-gray-500 mb-1">الحالة</p>
                @php $sc = ['draft'=>'gray','active'=>'green','completed'=>'blue','terminated'=>'red'][$contract->status]??'gray' @endphp
                <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ ucfirst($contract->status) }}</span>
            </div>
            <div><p class="text-gray-500 mb-1">تاريخ البداية</p><p class="font-medium">{{ \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') }}</p></div>
            <div><p class="text-gray-500 mb-1">تاريخ الانتهاء</p><p class="font-medium">{{ $contract->end_date ? \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d') : '-' }}</p></div>
        </div>
        @if($contract->payment_terms)
        <div class="border-t pt-4">
            <h3 class="font-semibold text-gray-700 mb-2">شروط الدفع</h3>
            <p class="text-gray-600 text-sm">{{ $contract->payment_terms }}</p>
        </div>
        @endif
        @if($contract->contract_file_path)
        <div class="border-t pt-4 mt-4">
            <a href="{{ Storage::url($contract->contract_file_path) }}" target="_blank" class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2 rounded-xl text-sm hover:bg-blue-100 transition">
                <i class="fas fa-file-pdf"></i> تحميل العقد
            </a>
        </div>
        @endif
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-sm space-y-3 h-fit">
        <div><p class="text-gray-500 mb-1">أنشئ بواسطة</p><p class="font-medium">{{ $contract->creator?->name }}</p></div>
        <div><p class="text-gray-500 mb-1">تاريخ الإنشاء</p><p>{{ $contract->created_at->format('Y-m-d') }}</p></div>
        @if($contract->signed_by_client)
        <div class="flex items-center gap-2 text-green-600"><i class="fas fa-check-circle"></i><span>موقّع من العميل</span></div>
        @endif
    </div>
</div>
@endsection
