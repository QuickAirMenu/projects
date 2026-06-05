@extends('layouts.app')
@section('title', __('messages.settings'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.settings') }}</h1>
</div>

@include('layouts.components.alert')

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

    {{-- Sidebar Nav --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-1 h-fit">
        <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-blue-50 text-blue-700 font-medium text-sm">
            <i class="fas fa-cog w-4"></i> الإعدادات العامة
        </a>
        <a href="{{ route('settings.service-types.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-gray-50 text-gray-700 text-sm transition">
            <i class="fas fa-briefcase w-4"></i> أنواع الخدمات
        </a>
        <a href="{{ route('settings.project-statuses.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-gray-50 text-gray-700 text-sm transition">
            <i class="fas fa-flag w-4"></i> حالات المشاريع
        </a>
        <a href="{{ route('settings.task-statuses.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-gray-50 text-gray-700 text-sm transition">
            <i class="fas fa-tags w-4"></i> حالات المهام
        </a>
        <a href="{{ route('roles.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-gray-50 text-gray-700 text-sm transition">
            <i class="fas fa-shield-alt w-4"></i> الأدوار والصلاحيات
        </a>
    </div>

    {{-- General Settings Form --}}
    <div class="lg:col-span-3 space-y-6">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
                <i class="fas fa-building text-blue-500"></i> معلومات الشركة
            </h2>
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([
                        ['key' => 'company_name',    'label' => 'اسم الشركة (EN)',    'type' => 'text'],
                        ['key' => 'company_name_ar', 'label' => 'اسم الشركة (AR)',    'type' => 'text'],
                        ['key' => 'company_email',   'label' => 'البريد الإلكتروني',  'type' => 'email'],
                        ['key' => 'company_phone',   'label' => 'رقم الهاتف',          'type' => 'text'],
                        ['key' => 'company_website', 'label' => 'الموقع الإلكتروني',  'type' => 'url'],
                        ['key' => 'company_address', 'label' => 'العنوان',             'type' => 'text'],
                    ] as $field)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $field['label'] }}</label>
                        <input type="{{ $field['type'] }}" name="{{ $field['key'] }}"
                               value="{{ $settings['general'][$field['key']] ?? '' }}"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @endforeach
                </div>
                <button type="submit" class="mt-5 bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl transition text-sm">
                    <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
                <i class="fas fa-receipt text-green-500"></i> الإعدادات المالية
            </h2>
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([
                        ['key' => 'default_tax_rate', 'label' => 'نسبة الضريبة الافتراضية %', 'type' => 'number'],
                        ['key' => 'currency',         'label' => 'رمز العملة',                 'type' => 'text'],
                        ['key' => 'currency_symbol',  'label' => 'رمز العملة المختصر',         'type' => 'text'],
                        ['key' => 'bank_account_name','label' => 'اسم صاحب الحساب البنكي',    'type' => 'text'],
                        ['key' => 'bank_iban',        'label' => 'رقم الآيبان',                'type' => 'text'],
                    ] as $field)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $field['label'] }}</label>
                        <input type="{{ $field['type'] }}" name="{{ $field['key'] }}"
                               value="{{ $settings['finance'][$field['key']] ?? '' }}"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @endforeach
                </div>
                <button type="submit" class="mt-5 bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2.5 rounded-xl transition text-sm">
                    <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
