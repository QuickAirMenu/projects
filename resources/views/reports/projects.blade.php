@extends('layouts.app')
@section('title', __('messages.projects_report'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.projects_report') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ __('messages.by_status_priority') }}</p>
    </div>
    <a href="{{ route('reports.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

{{-- Summary Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => __('project.total'),  'val' => $stats['total'],     'color' => 'blue',  'icon' => 'fa-folder'],
        ['label' => __('project.active_projects'),     'val' => $stats['in_progress'],'color' => 'green', 'icon' => 'fa-play-circle'],
        ['label' => __('project.overdue'),    'val' => $stats['overdue'],   'color' => 'red',   'icon' => 'fa-clock'],
        ['label' => __('project.completed'),    'val' => $stats['completed'], 'color' => 'purple','icon' => 'fa-check-circle'],
    ] as $c)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-{{ $c['color'] }}-100 flex items-center justify-center">
            <i class="fas {{ $c['icon'] }} text-{{ $c['color'] }}-600"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $c['val'] }}</p>
            <p class="text-xs text-gray-500">{{ $c['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <select name="status_id" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('messages.status') }}</option>
            @foreach($statuses as $s)
                <option value="{{ $s->id }}" {{ request('status_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm transition">
            <i class="fas fa-search me-1"></i>{{ __('messages.filter') }}
        </button>
        <a href="{{ route('reports.projects') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-xl text-sm transition">{{ __('messages.reset') }}</a>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b">
                <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('project.project') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">{{ __('client.client') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">{{ __('project.status') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('project.progress') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('project.budget_label') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">{{ __('project.deadline') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($projects as $project)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3">
                    <a href="{{ route('projects.show', $project) }}" class="font-medium text-gray-800 hover:text-blue-600">{{ $project->name }}</a>
                    <p class="text-xs text-gray-400">{{ $project->manager?->name }}</p>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $project->client?->name }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium" style="background:{{ $project->status?->color }}20;color:{{ $project->status?->color }}">
                        {{ $project->status?->name }}
                    </span>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 bg-gray-200 rounded-full h-1.5"><div class="bg-blue-500 h-1.5 rounded-full" style="width:{{ $project->progress_percentage }}%"></div></div>
                        <span class="text-xs text-gray-500">{{ $project->progress_percentage }}%</span>
                    </div>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell text-gray-600">{{ $project->budget ? number_format($project->budget,0) : '-' }}</td>
                <td class="px-4 py-3 hidden xl:table-cell">
                    <span class="{{ $project->deadline?->isPast() && !in_array($project->status?->slug??'',['completed','closed','cancelled']) ? 'text-red-500 font-medium' : 'text-gray-500' }}">
                        {{ $project->deadline?->format('Y-m-d') ?? '-' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-12 text-center text-gray-400">{{ __('messages.no_data_found') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($projects->hasPages())
    <div class="px-5 py-4 border-t">{{ $projects->links() }}</div>
    @endif
</div>
@endsection
