@extends('layouts.app')
@section('title', __('project.kanban') ?? 'Kanban')

@section('content')
<div class="mb-5 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('project.kanban') ?? 'لوحة كانبان' }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('projects.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
            <i class="fas fa-list me-2"></i>{{ __('messages.view') }}
        </a>
    </div>
</div>

{{-- Filter by status is handled by grouping --}}
<div class="flex gap-4 overflow-x-auto pb-4">
    @foreach($statuses as $status)
    <div class="flex-shrink-0 w-72 bg-gray-50 rounded-2xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full" style="background:{{ $status->color }}"></div>
                <h3 class="font-semibold text-gray-700 text-sm">{{ $status->name }}</h3>
            </div>
            <span class="bg-white border text-xs text-gray-500 px-2 py-0.5 rounded-full">
                {{ isset($projects[$status->id]) ? $projects[$status->id]->count() : 0 }}
            </span>
        </div>
        <div class="space-y-3 min-h-20">
            @foreach($projects[$status->id] ?? [] as $project)
            <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
                <a href="{{ route('projects.show', $project) }}" class="font-medium text-gray-800 text-sm hover:text-blue-600 block mb-1">{{ $project->name }}</a>
                <p class="text-xs text-gray-400 mb-2">{{ $project->client?->full_name }}</p>
                <div class="flex items-center justify-between">
                    @php $pc = ['low'=>'blue','medium'=>'yellow','high'=>'orange','urgent'=>'red'][$project->priority] ?? 'gray' @endphp
                    <span class="text-xs px-1.5 py-0.5 rounded-full bg-{{ $pc }}-100 text-{{ $pc }}-700">{{ __('project.priority_'.$project->priority) }}</span>
                    @if($project->deadline)
                    <span class="text-xs {{ $project->deadline->isPast() ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                        {{ $project->deadline->format('d/m') }}
                    </span>
                    @endif
                </div>
                <div class="mt-2 w-full bg-gray-100 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full bg-blue-500" style="width:{{ $project->progress_percentage }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection
