@extends('layouts.app')
@section('title', __('accounts.transfer'))

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('accounts.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm"><i class="fas fa-arrow-{{ is_rtl() ? 'right' : 'left' }}"></i></a>
    <h1 class="text-2xl font-bold text-gray-800">{{ __('accounts.transfer') }}</h1>
</div>

<form action="{{ route('accounts.transfer.store') }}" method="POST" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.from') }}</label>
            <select name="from_account_id" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                @foreach($accounts as $account)
                <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->currency }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.to') }}</label>
            <select name="to_account_id" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                @foreach($accounts as $account)
                <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->currency }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.amount_out') }}</label>
            <input name="amount_out" type="number" step="0.01" min="0.01" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.amount_in') }}</label>
            <input name="amount_in" type="number" step="0.01" min="0.01" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
            <p class="mt-1 text-[11px] text-gray-400">{{ __('accounts.amount_in_hint') }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.date') }}</label>
            <input name="transaction_date" type="date" value="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.description') }}</label>
            <input name="description" maxlength="300" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
    </div>
    <div class="flex justify-end gap-2">
        <a href="{{ route('accounts.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm">{{ __('messages.cancel') }}</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium">{{ __('accounts.transfer') }}</button>
    </div>
</form>
@endsection
