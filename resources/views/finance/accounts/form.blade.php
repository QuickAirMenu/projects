@extends('layouts.app')
@section('title', $account->exists ? __('accounts.edit') : __('accounts.new'))

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('accounts.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm"><i class="fas fa-arrow-{{ is_rtl() ? 'right' : 'left' }}"></i></a>
    <h1 class="text-2xl font-bold text-gray-800">{{ $account->exists ? __('accounts.edit') : __('accounts.new') }}</h1>
</div>

<form action="{{ $account->exists ? route('accounts.update', $account) : route('accounts.store') }}" method="POST" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
    @csrf
    @if($account->exists) @method('PUT') @endif
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.name') }}</label>
            <input name="name" required maxlength="120" value="{{ old('name', $account->name) }}" placeholder="{{ __('accounts.name_placeholder') }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.type') }}</label>
            <select name="type" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                @foreach(\App\Models\Account::TYPES as $accountType)
                <option value="{{ $accountType }}" @selected(old('type', $account->type) === $accountType)>{{ __('accounts.type_'.$accountType) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.currency') }}</label>
            <input name="currency" required maxlength="3" value="{{ old('currency', $account->currency ?? 'SAR') }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm uppercase">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.exchange_rate') }}</label>
            <input name="exchange_rate_to_base" type="number" step="0.000001" min="0.000001" value="{{ old('exchange_rate_to_base', $account->exchange_rate_to_base ?? 1) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
            <p class="mt-1 text-[11px] text-gray-400">{{ __('accounts.exchange_rate_hint') }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.opening_balance') }}</label>
            <input name="opening_balance" type="number" step="0.01" value="{{ old('opening_balance', $account->opening_balance ?? 0) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.bank_name') }}</label>
            <input name="bank_name" maxlength="120" value="{{ old('bank_name', $account->bank_name) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.account_number') }}</label>
            <input name="account_number" maxlength="60" value="{{ old('account_number', $account->account_number) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">IBAN</label>
            <input name="iban" maxlength="60" value="{{ old('iban', $account->iban) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.notes') }}</label>
            <textarea name="notes" rows="2" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">{{ old('notes', $account->notes) }}</textarea>
        </div>
        <div class="md:col-span-3">
            <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $account->is_active ?? true)) class="rounded border-gray-300">{{ __('accounts.active') }}</label>
        </div>
    </div>
    <div class="flex justify-end gap-2">
        <a href="{{ route('accounts.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm">{{ __('messages.cancel') }}</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium">{{ __('messages.save') }}</button>
    </div>
</form>
@endsection
