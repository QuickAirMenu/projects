@extends('layouts.app')
@section('title', __('accounts.title'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('accounts.title') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('accounts.hint') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('accounts.transfer.form') }}" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2.5 rounded-xl text-sm"><i class="fas fa-right-left me-2"></i>{{ __('accounts.transfer') }}</a>
        <a href="{{ route('accounts.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium"><i class="fas fa-plus me-2"></i>{{ __('accounts.new') }}</a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs text-gray-500">{{ __('accounts.total_base', ['currency' => $baseCurrency]) }}</p>
        <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($totalBase, 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($accounts as $account)
    <a href="{{ route('accounts.show', $account) }}" class="block rounded-2xl border border-gray-100 bg-white p-5 shadow-sm hover:border-blue-200 transition">
        <div class="flex items-start justify-between">
            <div>
                <p class="font-semibold text-gray-800">{{ $account->name }}</p>
                <p class="text-xs text-gray-400">{{ __('accounts.type_'.$account->type) }} · {{ $account->currency }} @if($account->bank_name) · {{ $account->bank_name }} @endif</p>
            </div>
            @unless($account->is_active)<span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">{{ __('accounts.inactive') }}</span>@endunless
        </div>
        <p class="mt-4 text-2xl font-bold {{ $account->balance() >= 0 ? 'text-gray-800' : 'text-red-600' }}">{{ number_format($account->balance(), 2) }} <span class="text-sm font-normal text-gray-400">{{ $account->currency }}</span></p>
        <p class="mt-1 text-xs text-gray-400">≈ {{ number_format($account->baseBalance(), 2) }} {{ $baseCurrency }} · {{ $account->transactions_count }} {{ __('accounts.movements') }}</p>
    </a>
    @empty
    <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-gray-200 bg-white p-10 text-center text-gray-400">{{ __('accounts.empty') }}</div>
    @endforelse
</div>
@endsection
