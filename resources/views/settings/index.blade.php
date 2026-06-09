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
            <i class="fas fa-cog w-4"></i> {{ __('settings.general_settings') }}
        </a>
        <a href="{{ route('settings.service-types.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-gray-50 text-gray-700 text-sm transition">
            <i class="fas fa-briefcase w-4"></i> {{ __('settings.service_types') }}
        </a>
        <a href="{{ route('settings.project-statuses.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-gray-50 text-gray-700 text-sm transition">
            <i class="fas fa-flag w-4"></i> {{ __('settings.project_statuses') }}
        </a>
        <a href="{{ route('settings.task-statuses.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-gray-50 text-gray-700 text-sm transition">
            <i class="fas fa-tags w-4"></i> {{ __('settings.task_statuses') }}
        </a>
        <a href="{{ route('roles.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-gray-50 text-gray-700 text-sm transition">
            <i class="fas fa-shield-alt w-4"></i> {{ __('settings.roles_permissions') }}
        </a>
    </div>

    {{-- General Settings Form --}}
    <div class="lg:col-span-3 space-y-6">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
                <i class="fas fa-building text-blue-500"></i> {{ __('settings.company_info') }}
            </h2>
            <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([
                        ['key' => 'company_name',    'label' => __('settings.company_name_en'),    'type' => 'text'],
                        ['key' => 'company_name_ar', 'label' => __('settings.company_name_ar'),    'type' => 'text'],
                        ['key' => 'company_email',   'label' => __('users.email'),                  'type' => 'email'],
                        ['key' => 'company_phone',   'label' => __('users.phone'),                  'type' => 'text'],
                        ['key' => 'company_website', 'label' => __('settings.website'),             'type' => 'url'],
                        ['key' => 'company_address', 'label' => __('settings.address'),             'type' => 'text'],
                    ] as $field)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $field['label'] }}</label>
                        <input type="{{ $field['type'] }}" name="{{ $field['key'] }}"
                               value="{{ $settings['general'][$field['key']] ?? '' }}"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @endforeach
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('settings.company_logo') }}</label>
                        <input type="file" name="company_logo" accept=".jpg,.jpeg,.png,.webp,.svg"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-400">{{ __('settings.logo_requirements') }}</p>
                        @if(workspace_config('company_logo'))
                            <div class="mt-3 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 p-3">
                                <img src="{{ workspace_config('company_logo') }}" alt="Company logo" class="h-14 w-auto rounded-lg bg-white object-contain p-2">
                                <div class="text-xs text-gray-500">
                                    <p class="font-medium text-gray-700">{{ __('settings.current_logo') }}</p>
                                    <p>{{ __('settings.logo_replacement_note') }}</p>
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
                <i class="fas fa-receipt text-green-500"></i> {{ __('settings.finance_settings') }}
            </h2>
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([
                        ['key' => 'default_tax_rate', 'label' => __('settings.default_tax_rate'), 'type' => 'number'],
                        ['key' => 'currency',         'label' => __('settings.currency_code'), 'type' => 'text'],
                        ['key' => 'currency_symbol',  'label' => __('settings.currency_symbol'), 'type' => 'text'],
                        ['key' => 'bank_account_name','label' => __('settings.bank_account_name'), 'type' => 'text'],
                        ['key' => 'bank_iban',        'label' => __('settings.bank_iban'), 'type' => 'text'],
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
                <i class="fas fa-envelope text-purple-500"></i> {{ __('settings.mail_settings') }}
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
                            <input type="password" name="{{ $field['key'] }}" value="" placeholder="{{ __('settings.leave_blank_to_keep') }}"
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
