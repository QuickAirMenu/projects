@extends('layouts.app')
@section('title', __('reports.wip_title'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('reports.wip_title') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('reports.wip_hint') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reports.wip.export') }}" class="bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-700 rounded-xl">
            <i class="fas fa-file-csv me-2"></i>{{ __('reports.export_csv') }}
        </a>
        <a href="{{ route('reports.index') }}" class="rounded-xl bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200">
            <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
        </a>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => __('reports.wip_hours'), 'val' => number_format($totals['hours'], 2), 'color' => 'blue'],
        ['label' => __('reports.wip_labor'), 'val' => number_format($totals['labor'], 2), 'color' => 'amber'],
        ['label' => __('reports.wip_expenses'), 'val' => number_format($totals['expenses'], 2), 'color' => 'red'],
        ['label' => __('reports.wip_total'), 'val' => number_format($totals['total'], 2), 'color' => 'emerald'],
    ] as $card)
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs text-gray-500">{{ $card['label'] }}</p>
        <p class="mt-2 text-2xl font-bold text-{{ $card['color'] }}-600">{{ $card['val'] }}</p>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
            <tr>
                <th class="px-5 py-3 text-start">{{ __('project.project') }}</th>
                <th class="px-5 py-3 text-start">{{ __('client.client') }}</th>
                <th class="px-5 py-3 text-end">{{ __('reports.wip_hours') }}</th>
                <th class="px-5 py-3 text-end">{{ __('reports.wip_labor') }}</th>
                <th class="px-5 py-3 text-end">{{ __('reports.wip_expenses') }}</th>
                <th class="px-5 py-3 text-end">{{ __('reports.wip_total') }}</th>
                <th class="px-5 py-3 text-end">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($rows as $row)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3"><a href="{{ route('projects.show', $row['id']) }}" class="font-medium text-gray-800 hover:text-primary-600">{{ $row['project'] }}</a></td>
                <td class="px-5 py-3 text-gray-600">{{ $row['client'] }}</td>
                <td class="px-5 py-3 text-end text-gray-700">{{ number_format($row['hours'], 2) }}</td>
                <td class="px-5 py-3 text-end text-amber-600">{{ number_format($row['labor'], 2) }}</td>
                <td class="px-5 py-3 text-end text-red-500">{{ number_format($row['expenses'], 2) }}</td>
                <td class="px-5 py-3 text-end font-bold text-emerald-600">{{ number_format($row['total'], 2) }}</td>
                <td class="px-5 py-3 text-end whitespace-nowrap">
                    @if($row['labor'] > 0)
                    <a href="{{ route('time-entries.bill.preview', ['project_id' => $row['id']]) }}" class="text-xs text-blue-600 hover:underline me-2">{{ __('reports.wip_bill_hours') }}</a>
                    @endif
                    @if($row['expenses'] > 0)
                    <a href="{{ route('expenses.bill.preview', ['project_id' => $row['id']]) }}" class="text-xs text-emerald-600 hover:underline">{{ __('reports.wip_bill_expenses') }}</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-16 text-center text-gray-400">{{ __('reports.wip_empty') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
