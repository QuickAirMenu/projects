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
    <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
        <p class="text-xs text-gray-500">{{ __('accounts.total_assets', ['currency' => $baseCurrency]) }}</p>
        <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($assetsTotal, 2) }}</p>
    </div>
    <div class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm">
        <p class="text-xs text-gray-500">{{ __('accounts.total_liabilities', ['currency' => $baseCurrency]) }}</p>
        <p class="mt-2 text-2xl font-bold text-red-600">{{ number_format(abs($liabilitiesTotal), 2) }}</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs text-gray-500">{{ __('accounts.net_treasury', ['currency' => $baseCurrency]) }}</p>
        <p class="mt-2 text-2xl font-bold {{ $netTotal >= 0 ? 'text-gray-800' : 'text-red-600' }}">{{ number_format($netTotal, 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
        <p class="text-xs text-gray-500">{{ __('accounts.inflow_month') }} ({{ $baseCurrency }})</p>
        <p class="mt-2 text-2xl font-bold text-blue-600">+{{ number_format($monthIn, 2) }}</p>
    </div>
    <div class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm">
        <p class="text-xs text-gray-500">{{ __('accounts.outflow_month') }} ({{ $baseCurrency }})</p>
        <p class="mt-2 text-2xl font-bold text-red-600">−{{ number_format($monthOut, 2) }}</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs text-gray-500">{{ __('accounts.net_month') }} ({{ $baseCurrency }})</p>
        <p class="mt-2 text-2xl font-bold {{ $monthIn - $monthOut >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($monthIn - $monthOut, 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="lg:col-span-2 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-sm font-semibold text-gray-800 mb-4">{{ __('accounts.last_6_months') }}</p>
        <div class="flex items-end justify-between gap-3 h-40">
            @foreach($chart as $month)
            <div class="flex-1 flex flex-col items-center gap-2">
                <div class="w-full flex items-end justify-center gap-1 h-28">
                    <div class="w-1/3 rounded-t bg-blue-400" style="height: {{ max(2, round($month['in'] / $chartMax * 100)) }}%"></div>
                    <div class="w-1/3 rounded-t bg-red-300" style="height: {{ max(2, round($month['out'] / $chartMax * 100)) }}%"></div>
                </div>
                <span class="text-[10px] text-gray-400">{{ $month['label'] }}</span>
            </div>
            @endforeach
        </div>
        <div class="mt-3 flex items-center gap-4 text-[11px] text-gray-500">
            <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-blue-400"></span>{{ __('accounts.in') }}</span>
            <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-red-300"></span>{{ __('accounts.out') }}</span>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-sm font-semibold text-gray-800 mb-3">{{ __('accounts.by_currency') }}</p>
        <div class="space-y-2 text-sm">
            @forelse($byCurrency as $row)
            <div class="flex items-center justify-between rounded-xl bg-gray-50 px-3 py-2">
                <span class="font-medium text-gray-700">{{ $row['currency'] }} <span class="text-xs text-gray-400">({{ $row['count'] }})</span></span>
                <span class="font-semibold {{ $row['balance'] >= 0 ? 'text-gray-800' : 'text-red-600' }}">{{ number_format($row['balance'], 2) }}</span>
            </div>
            @empty
            <p class="text-gray-400 text-sm">—</p>
            @endforelse
        </div>
    </div>
</div>

<div class="rounded-2xl border border-gray-100 bg-white shadow-sm mb-6">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <p class="text-sm font-semibold text-gray-800">{{ __('accounts.recent_movements') }}</p>
        <a href="{{ route('accounts.transfer.form') }}" class="text-xs text-blue-600 hover:underline">{{ __('accounts.transfer') }}</a>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse($recent as $tx)
        <div class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
            <div class="min-w-0">
                <p class="text-gray-700 truncate">{{ $tx->description ?: __('accounts.cat_'.$tx->category) }}</p>
                <p class="text-xs text-gray-400">{{ $tx->account?->name }} · {{ $tx->transaction_date?->format('Y-m-d') }}</p>
            </div>
            <div class="text-end shrink-0">
                <p class="font-medium {{ $tx->direction === 'in' ? 'text-emerald-600' : 'text-red-500' }}">
                    {{ $tx->direction === 'in' ? '+' : '−' }}{{ number_format((float) $tx->amount, 2) }} {{ $tx->currency }}
                </p>
                <p class="text-[11px] text-gray-400">≈ {{ number_format((float) $tx->base_amount, 2) }} {{ $baseCurrency }}</p>
            </div>
        </div>
        @empty
        <p class="px-5 py-8 text-center text-sm text-gray-400">{{ __('accounts.no_movements') }}</p>
        @endforelse
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($accounts as $account)
    <a href="{{ route('accounts.show', $account) }}" class="block rounded-2xl border border-{{ $account->color() }}-100 bg-white p-5 shadow-sm hover:border-{{ $account->color() }}-300 transition">
        <div class="flex items-start justify-between gap-2">
            <div class="flex items-start gap-3 min-w-0">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-{{ $account->color() }}-100 text-{{ $account->color() }}-600">
                    <i class="fas {{ $account->icon() }}"></i>
                </span>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-800 truncate">{{ $account->name }}</p>
                    <p class="text-xs text-gray-400">{{ __('accounts.type_'.$account->type) }} · {{ $account->currency }} @if($account->bank_name) · {{ $account->bank_name }} @endif</p>
                </div>
            </div>
            <span class="shrink-0 rounded-full bg-{{ $account->color() }}-100 px-2 py-0.5 text-[11px] text-{{ $account->color() }}-700">
                {{ $account->nature() === 'liability' ? '− ' : '+ ' }}{{ __('accounts.nature_'.$account->nature()) }}
            </span>
        </div>
        <p class="mt-4 text-2xl font-bold {{ $account->balance() >= 0 ? 'text-gray-800' : 'text-red-600' }}">{{ number_format($account->balance(), 2) }} <span class="text-sm font-normal text-gray-400">{{ $account->currency }}</span></p>
        <p class="mt-1 text-xs text-gray-400">≈ {{ number_format($account->baseBalance(), 2) }} {{ $baseCurrency }} · {{ $account->transactions_count }} {{ __('accounts.movements') }}</p>
        @unless($account->is_active)<span class="mt-2 inline-block rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">{{ __('accounts.inactive') }}</span>@endunless
    </a>
    @empty
    <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-gray-200 bg-white p-10 text-center text-gray-400">{{ __('accounts.empty') }}</div>
    @endforelse
</div>
@endsection
