@extends('layouts.app')

@section('title', __('reports.aging_title'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'common.reports', 'url' => route('reports.index')],
        ['label' => 'reports.aging_title', 'url' => null],
    ]" :show-home="false" />
@endsection

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('reports.aging_title') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('reports.aging_hint') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('reports.aging.export') }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-700">
                <i class="fas fa-file-excel me-2"></i>{{ __('messages.export') }}
            </a>
            <a href="{{ route('reports.index') }}" class="rounded-xl bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200">
                <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
            </a>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        @foreach([
            ['label' => __('reports.aging_current'), 'value' => $totals['current'], 'tone' => 'emerald'],
            ['label' => __('reports.aging_1_30'), 'value' => $totals['d1_30'], 'tone' => 'blue'],
            ['label' => __('reports.aging_31_60'), 'value' => $totals['d31_60'], 'tone' => 'amber'],
            ['label' => __('reports.aging_61_90'), 'value' => $totals['d61_90'], 'tone' => 'orange'],
            ['label' => __('reports.aging_90_plus'), 'value' => $totals['d90_plus'], 'tone' => 'red'],
        ] as $kpi)
            <div class="card rounded-2xl p-5">
                <p class="text-xs text-gray-500">{{ $kpi['label'] }}</p>
                <p class="mt-1 text-lg font-bold text-{{ $kpi['tone'] }}-600">{{ number_format($kpi['value'], 2) }}</p>
            </div>
        @endforeach
    </div>

    <div class="card overflow-hidden rounded-2xl">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-gray-500">
                    <th class="px-5 py-3 font-semibold">{{ __('finance.invoice_number') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('client.client') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('finance.due_date') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('reports.days_overdue') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('reports.bucket') }}</th>
                    <th class="px-5 py-3 text-end font-semibold">{{ __('finance.remaining') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rows as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $row['invoice_number'] }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $row['client'] }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $row['due_date'] }}</td>
                        <td class="px-5 py-3 {{ $row['days_overdue'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-500' }}">{{ $row['days_overdue'] }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $row['bucket_label'] }}</td>
                        <td class="px-5 py-3 text-end font-semibold text-gray-800">{{ number_format($row['remaining'], 2) }} {{ __('messages.currency') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-16 text-center text-gray-400">{{ __('messages.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if(count($rows))
            <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-5 py-3 text-sm font-bold text-gray-800">
                <span>{{ __('reports.total_outstanding') }}</span>
                <span>{{ number_format($totals['total'], 2) }} {{ __('messages.currency') }}</span>
            </div>
        @endif
    </div>
@endsection
