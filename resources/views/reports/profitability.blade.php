@extends('layouts.app')

@section('title', __('reports.profitability_title'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'common.reports', 'url' => route('reports.index')],
        ['label' => 'reports.profitability_title', 'url' => null],
    ]" :show-home="false" />
@endsection

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('reports.profitability_title') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('reports.profitability_hint') }}</p>
        </div>
        <a href="{{ route('reports.index') }}" class="rounded-xl bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200">
            <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
        </a>
    </div>

    <div class="card overflow-hidden rounded-2xl">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-gray-500">
                    <th class="px-5 py-3 font-semibold">{{ __('project.project') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('client.client') }}</th>
                    <th class="px-5 py-3 text-end font-semibold">{{ __('reports.invoiced') }}</th>
                    <th class="px-5 py-3 text-end font-semibold">{{ __('reports.collected') }}</th>
                    <th class="px-5 py-3 text-end font-semibold">{{ __('reports.outstanding') }}</th>
                    <th class="px-5 py-3 text-end font-semibold">{{ __('reports.collection_rate') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($projects as $project)
                    @php
                        $invoiced = (float) ($project->invoiced_total ?? 0);
                        $collected = (float) ($collectedByProject[$project->id] ?? 0);
                        $outstanding = max(0, $invoiced - $collected);
                        $rate = $invoiced > 0 ? round($collected / $invoiced * 100) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3"><a href="{{ route('projects.show', $project) }}" class="font-medium text-gray-800 hover:text-primary-600">{{ $project->name }}</a></td>
                        <td class="px-5 py-3 text-gray-600">{{ $project->client?->name ?? '-' }}</td>
                        <td class="px-5 py-3 text-end text-gray-800">{{ number_format($invoiced, 2) }}</td>
                        <td class="px-5 py-3 text-end text-emerald-600">{{ number_format($collected, 2) }}</td>
                        <td class="px-5 py-3 text-end {{ $outstanding > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ number_format($outstanding, 2) }}</td>
                        <td class="px-5 py-3 text-end">
                            <span class="inline-flex items-center gap-2">
                                <span class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-200"><span class="block h-1.5 rounded-full bg-emerald-500" style="width: {{ $rate }}%"></span></span>
                                <span class="text-xs text-gray-500">{{ $rate }}%</span>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-16 text-center text-gray-400">{{ __('messages.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($projects->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">{{ $projects->links() }}</div>
        @endif
    </div>
@endsection
