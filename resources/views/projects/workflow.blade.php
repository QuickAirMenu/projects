@extends('layouts.app')
@section('title', __('project.workflow'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('project.workflow') }}</h1>
        <p class="text-gray-500 text-sm">{{ $project->name }}</p>
    </div>
    <a href="{{ route('projects.show', $project) }}" class="flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-xl transition text-sm">
        <i class="fas fa-arrow-right"></i> {{ __('messages.back') }}
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    @forelse($steps as $step)
    <div class="flex items-center gap-4 py-4 border-b last:border-0">
        <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-lg
            {{ $step->completed ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}">
            <i class="fas {{ $step->completed ? 'fa-check-circle' : 'fa-circle' }}"></i>
        </div>
        <div class="flex-1">
            <p class="font-medium text-gray-800 {{ $step->completed ? 'line-through text-gray-400' : '' }}">
                {{ $step->step_name }}
            </p>
            @if($step->completed && $step->completedBy)
            <p class="text-xs text-gray-400">{{ $step->completedBy->name }} · {{ $step->completed_at?->format('Y-m-d H:i') }}</p>
            @endif
        </div>
        @can('update', $project)
        <form action="{{ $step->completed ? route('projects.workflow.undo', [$project, $step]) : route('projects.workflow.complete', [$project, $step]) }}" method="POST">
            @csrf
            <button type="submit" class="text-sm px-3 py-1.5 rounded-lg transition
                {{ $step->completed ? 'bg-gray-100 text-gray-600 hover:bg-gray-200' : 'bg-blue-600 text-white hover:bg-blue-700' }}">
                {{ $step->completed ? __('project.workflow_undo') : __('project.workflow_complete') }}
            </button>
        </form>
        @endcan
    </div>
    @empty
    <p class="text-gray-400 text-sm text-center py-8">{{ __('project.no_workflow_steps') }}</p>
    @endforelse
</div>
@endsection
