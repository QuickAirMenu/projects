@extends('layouts.app')
@section('title', __('messages.settings'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.settings') }}</h1>
</div>


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
            <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
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
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">شعار الشركة</label>
                        <input type="file" name="company_logo" accept=".jpg,.jpeg,.png,.webp,.svg"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-400">jpg, jpeg, png, webp, svg - حتى 2MB</p>
                        @if(workspace_config('company_logo'))
                            <div class="mt-3 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 p-3">
                                <img src="{{ workspace_config('company_logo') }}" alt="Company logo" class="h-14 w-auto rounded-lg bg-white object-contain p-2">
                                <div class="text-xs text-gray-500">
                                    <p class="font-medium text-gray-700">الشعار الحالي</p>
                                    <p>سيتم استبداله عند رفع ملف جديد.</p>
                                </div>
                            </div>
                        @endif
                    </div>
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

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
                <i class="fas fa-envelope text-purple-500"></i> إعدادات البريد الإلكتروني
            </h2>
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([
                        ['key' => 'mail_host', 'label' => 'Mail Host', 'type' => 'text'],
                        ['key' => 'mail_port', 'label' => 'Mail Port', 'type' => 'number'],
                        ['key' => 'mail_encryption', 'label' => 'Encryption', 'type' => 'select'],
                        ['key' => 'mail_username', 'label' => 'Mail Username', 'type' => 'text'],
                        ['key' => 'mail_password', 'label' => 'Mail Password', 'type' => 'password'],
                        ['key' => 'mail_from_address', 'label' => 'From Address', 'type' => 'email'],
                        ['key' => 'mail_from_name', 'label' => 'From Name', 'type' => 'text'],
                    ] as $field)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $field['label'] }}</label>
                        @if($field['type'] === 'select')
                            <select name="{{ $field['key'] }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach(['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'None'] as $value => $label)
                                    <option value="{{ $value }}" {{ ($settings['mail'][$field['key']] ?? 'ssl') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif($field['type'] === 'password')
                            <input type="password" name="{{ $field['key'] }}" value="" placeholder="اتركه فارغاً للاحتفاظ بالقيمة الحالية"
                                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @else
                            <input type="{{ $field['type'] }}" name="{{ $field['key'] }}"
                                   value="{{ $settings['mail'][$field['key']] ?? '' }}"
                                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @endif
                    </div>
                    @endforeach
                </div>
                <button type="submit" class="mt-5 bg-purple-600 hover:bg-purple-700 text-white font-medium px-6 py-2.5 rounded-xl transition text-sm">
                    <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
