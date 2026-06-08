@extends('layouts.app')
@section('title', __('finance.add_payment'))
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('finance.add_payment') }}</h1>
    <a href="{{ route('payments.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

<div class="max-w-xl bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <p class="text-gray-500 text-sm mb-5">لتسجيل دفعة، يرجى الذهاب إلى صفحة الفاتورة المرتبطة وتسجيل الدفعة من هناك.</p>
    <a href="{{ route('invoices.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition">
        <i class="fas fa-file-invoice me-2"></i>الذهاب إلى الفواتير
    </a>
</div>
@endsection
