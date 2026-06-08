@extends('layouts.app')
@section('title', __('project.projects'))

@section('content')
{{-- Header --}}
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('project.projects') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ __('messages.total') }}: {{ $stats['total'] }} {{ __('project.project') }}</p>
    </div>
    @can('create', App\Models\Project::class)
    <a href="{{ route('projects.create') }}" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl transition font-medium">
        <i class="fas fa-plus"></i> {{ __('project.add_project') }}
    </a>
    @endcan
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => 'الإجمالي',   'val' => $stats['total'],     'color' => 'blue',   'icon' => 'fa-folder'],
        ['label' => 'جارية',       'val' => $stats['active'],    'color' => 'green',  'icon' => 'fa-play-circle'],
        ['label' => 'متأخرة',      'val' => $stats['overdue'],   'color' => 'red',    'icon' => 'fa-exclamation-circle'],
        ['label' => 'مكتملة',      'val' => $stats['completed'], 'color' => 'purple', 'icon' => 'fa-check-circle'],
    ] as $card)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-{{ $card['color'] }}-100 flex items-center justify-center">
            <i class="fas {{ $card['icon'] }} text-{{ $card['color'] }}-600 text-xl"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $card['val'] }}</p>
            <p class="text-xs text-gray-500">{{ $card['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search') }}..."
               class="flex-1 min-w-48 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="status_id" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('project.status') }}</option>
            @foreach($statuses as $s)
                <option value="{{ $s->id }}" {{ request('status_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        <select name="priority" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('project.priority') }}</option>
            @foreach(['low','medium','high','urgent'] as $p)
                <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ __('project.priority_'.$p) }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm transition">
            <i class="fas fa-search me-1"></i>{{ __('messages.filter') }}
        </button>
        <a href="{{ route('projects.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-xl text-sm transition">{{ __('messages.reset') }}</a>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('project.project') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">{{ __('client.client') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('project.status') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('project.priority') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">{{ __('project.deadline') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">{{ __('project.progress') }}</th>
                <th class="text-end px-5 py-3 font-semibold text-gray-600">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($projects as $project)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3">
                    <a href="{{ route('projects.show', $project) }}" class="font-medium text-gray-800 hover:text-blue-600">{{ $project->name }}</a>
                    <p class="text-xs text-gray-400">{{ $project->project_number }}</p>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $project->client?->name ?? '-' }}</td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    <span class="px-2 py-1 rounded-full text-xs font-medium" style="background:{{ $project->status?->color }}20; color:{{ $project->status?->color }}">
                        {{ $project->status?->name }}
                    </span>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    @php $pc = ['low'=>'blue','medium'=>'yellow','high'=>'orange','urgent'=>'red'][$project->priority] ?? 'gray' @endphp
                    <span class="px-2 py-1 rounded-full text-xs bg-{{ $pc }}-100 text-{{ $pc }}-700">{{ __('project.priority_'.$project->priority) }}</span>
                </td>
                <td class="px-4 py-3 hidden xl:table-cell">
                    <span class="{{ $project->deadline?->isPast() && !in_array($project->status?->slug ?? '', ['completed','closed','cancelled']) ? 'text-red-500 font-medium' : 'text-gray-600' }}">
                        {{ $project->deadline?->format('Y-m-d') ?? '-' }}
                    </span>
                </td>
                <td class="px-4 py-3 hidden xl:table-cell">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                            <div class="bg-blue-600 h-1.5 rounded-full" style="width:{{ $project->progress_percentage }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500">{{ $project->progress_percentage }}%</span>
                    </div>
                </td>
                <td class="px-5 py-3 text-end">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('projects.show', $project) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="{{ __('messages.view') }}">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('update', $project)
                        <a href="{{ route('projects.edit', $project) }}" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg transition" title="{{ __('messages.edit') }}">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan
                        @can('delete', $project)
                        <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirmDelete(this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition" title="{{ __('messages.delete') }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="py-16 text-center text-gray-400">
                <i class="fas fa-folder-open text-5xl mb-3 block text-gray-200"></i>
                {{ __('project.no_projects') }}
            </td></tr>
            @endforelse
        </tbody>
    </table>

    @if($projects->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $projects->links() }}
    </div>
    @endif
</div>
@endsection
