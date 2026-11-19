@extends('layouts.app')
@section('title', __('task.payout_title'))

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ url()->previous() }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm"><i class="fas fa-arrow-{{ is_rtl() ? 'right' : 'left' }}"></i></a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('task.payout_title') }}</h1>
        <p class="text-sm text-gray-500">{{ $task->title }} · {{ number_format((float) $task->cost_amount, 2) }} {{ $task->cost_currency ?: $baseCurrency }}</p>
    </div>
</div>

@if($accounts->isEmpty())
<div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
    {{ __('accounts.empty') }} — <a href="{{ route('accounts.create') }}" class="underline">{{ __('accounts.new') }}</a>
</div>
@else
<form action="{{ route('tasks.cost.pay', $task) }}" method="POST" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.title') }}</label>
            <select name="account_id" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                @foreach($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>{{ $account->name }} — {{ $account->currency }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.amount') }}</label>
            <input name="amount" type="number" step="0.01" min="0.01" required value="{{ old('amount', (float) $task->cost_amount) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.exchange_rate') }}</label>
            <input name="exchange_rate" type="number" step="0.000001" min="0.000001" value="{{ old('exchange_rate', 1) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
            <p class="mt-1 text-[11px] text-gray-400">{{ __('accounts.payout_rate_hint') }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.date') }}</label>
            <input name="payout_date" type="date" value="{{ old('payout_date', now()->toDateString()) }}" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
    </div>
    <div class="flex justify-end gap-2">
        <a href="{{ url()->previous() }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm">{{ __('messages.cancel') }}</a>
        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium"><i class="fas fa-check me-2"></i>{{ __('task.mark_paid') }}</button>
    </div>
</form>
@endif
@endsection
