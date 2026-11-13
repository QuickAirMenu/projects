@extends('portal.layouts.app')

@section('title', __('portal.statement'))

@section('breadcrumb')
    <li class="text-gray-400 mx-1">/</li>
    <li class="text-gray-600 font-medium">{{ __('portal.statement') }}</li>
@endsection

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('portal.statement') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('portal.statement_hint') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('portal.statement.pdf', request()->only(['from', 'to'])) }}" target="_blank"
               class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm text-white transition hover:bg-red-700">
                <i class="fas fa-file-pdf"></i> {{ __('messages.download_pdf') }}
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('portal.statement') }}" class="card mb-6 flex flex-wrap items-end gap-3 rounded-2xl p-4">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('portal.from') }}</label>
            <input type="date" name="from" value="{{ $from }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('portal.to') }}</label>
            <input type="date" name="to" value="{{ $to }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
        </div>
        <button type="submit" class="rounded-xl bg-primary-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-primary-700">
            <i class="fas fa-filter me-1"></i>{{ __('messages.filter') }}
        </button>
    </form>

    {{-- KPIs --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach([
            ['label' => __('portal.total_invoiced'), 'value' => number_format($totals['invoiced'], 2), 'tone' => 'blue'],
            ['label' => __('portal.total_paid'), 'value' => number_format($totals['paid'], 2), 'tone' => 'emerald'],
            ['label' => __('portal.balance_due'), 'value' => number_format($totals['balance'], 2), 'tone' => 'red'],
        ] as $kpi)
            <div class="card rounded-2xl p-5">
                <p class="text-xs text-gray-500">{{ $kpi['label'] }}</p>
                <p class="mt-1 text-xl font-bold text-{{ $kpi['tone'] }}-600">{{ $kpi['value'] }} <span class="text-xs font-medium text-gray-400">{{ __('messages.currency') }}</span></p>
            </div>
        @endforeach
    </div>

    {{-- Ledger --}}
    <div class="card overflow-hidden rounded-2xl">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-gray-500">
                    <th class="px-5 py-3 font-semibold">{{ __('portal.date') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('portal.document') }}</th>
                    <th class="px-5 py-3 text-end font-semibold">{{ __('portal.debit') }}</th>
                    <th class="px-5 py-3 text-end font-semibold">{{ __('portal.credit') }}</th>
                    <th class="px-5 py-3 text-end font-semibold">{{ __('portal.running_balance') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rows as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-600">{{ $row['date'] }}</td>
                        <td class="px-5 py-3">
                            <span class="font-medium text-gray-800">{{ $row['label'] }}</span>
                            @if($row['type'] === 'payment')
                                <span class="ms-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700">{{ __('portal.payment') }}</span>
                            @else
                                <span class="ms-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700">{{ __('finance.invoice') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-end text-gray-800">{{ $row['debit'] ? number_format($row['debit'], 2) : '-' }}</td>
                        <td class="px-5 py-3 text-end text-emerald-600">{{ $row['credit'] ? number_format($row['credit'], 2) : '-' }}</td>
                        <td class="px-5 py-3 text-end font-semibold text-gray-900">{{ number_format($row['balance'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center text-gray-400">
                            <i class="fas fa-file-invoice-dollar mb-3 block text-4xl text-gray-300"></i>
                            {{ __('messages.no_data') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
