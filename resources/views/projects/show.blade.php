@extends('layouts.app')
@section('title', $project->name)

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-mono bg-gray-100 text-gray-500 px-2 py-1 rounded">{{ $project->project_number }}</span>
            <span class="px-3 py-1 rounded-full text-xs font-medium" style="background:{{ $project->status->color }}20; color:{{ $project->status->color }}">
                {{ $project->status->name }}
            </span>
            @php $priorityColors = ['low'=>'blue','medium'=>'yellow','high'=>'orange','urgent'=>'red']; @endphp
            <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $priorityColors[$project->priority] ?? 'gray' }}-100 text-{{ $priorityColors[$project->priority] ?? 'gray' }}-700">
                {{ __('project.priority_'.$project->priority) }}
            </span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mt-1">{{ $project->name }}</h1>
        <p class="text-gray-500 text-sm">{{ $project->client->name }}</p>
    </div>
    <div class="flex gap-2">
        @can('update', $project)
        <a href="{{ route('projects.edit', $project) }}" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl transition text-sm">
            <i class="fas fa-edit"></i> {{ __('messages.edit') }}
        </a>
        @endcan
        <a href="{{ route('projects.index') }}" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl transition text-sm">
            <i class="fas fa-arrow-right"></i> {{ __('messages.back') }}
        </a>
    </div>
</div>

@include('layouts.components.alert')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Progress Bar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">{{ __('project.progress') }}</span>
                <span class="text-sm font-bold text-blue-600">{{ $project->progress_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-blue-600 h-3 rounded-full transition-all" style="width: {{ $project->progress_percentage }}%"></div>
            </div>
        </div>

        {{-- Tasks --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2"><i class="fas fa-tasks text-blue-500"></i> {{ __('task.tasks') }}</h3>
                @can('create', App\Models\Task::class)
                <a href="{{ route('tasks.create') }}?project_id={{ $project->id }}" class="text-sm text-blue-600 hover:underline">+ {{ __('task.add_task') }}</a>
                @endcan
            </div>
            @forelse($project->tasks()->whereNull('parent_task_id')->with(['status','assignedUser'])->orderBy('due_date')->take(10)->get() as $task)
            <div class="flex items-center justify-between py-3 border-b last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full" style="background:{{ $task->status->color }}"></div>
                    <a href="{{ route('tasks.show', $task) }}" class="text-sm font-medium text-gray-800 hover:text-blue-600">{{ $task->title }}</a>
                </div>
                <div class="flex items-center gap-3 text-xs text-gray-400">
                    @if($task->assignedUser)<span>{{ $task->assignedUser->name }}</span>@endif
                    @if($task->due_date)
                    <span class="{{ $task->due_date->isPast() ? 'text-red-500 font-medium' : '' }}">{{ $task->due_date->format('d/m') }}</span>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4">{{ __('task.no_tasks') }}</p>
            @endforelse
        </div>

        {{-- Attachments --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-paperclip text-gray-500"></i> {{ __('project.attachments') }}</h3>
            <form action="{{ route('projects.attachments.store', $project) }}" method="POST" enctype="multipart/form-data" class="mb-4 flex gap-2">
                @csrf
                <input type="file" name="file" required class="flex-1 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none">
                <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-xl text-sm transition">
                    <i class="fas fa-upload me-1"></i>{{ __('messages.import') }}
                </button>
            </form>
            @forelse($project->attachments as $att)
            <div class="flex items-center justify-between py-2 border-b last:border-0">
                <div class="flex items-center gap-3">
                    <i class="fas fa-file text-blue-400"></i>
                    <div>
                        <p class="text-sm font-medium text-gray-700">{{ $att->file_name }}</p>
                        <p class="text-xs text-gray-400">{{ $att->uploader->name }} · {{ $att->created_at->format('Y-m-d') }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm"><i class="fas fa-download"></i></a>
                    <form action="{{ route('projects.attachments.destroy', $att) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-600 text-sm"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-3">{{ __('project.attachments') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">{{ __('client.client') }}</span><span class="font-medium">{{ $project->client->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('project.service_type') }}</span><span class="font-medium">{{ $project->serviceType->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('project.manager') }}</span><span class="font-medium">{{ $project->manager->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('project.start_date') }}</span><span class="font-medium">{{ $project->start_date?->format('Y-m-d') ?? '-' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('project.deadline') }}</span>
                <span class="font-medium {{ $project->deadline?->isPast() && !in_array($project->status->slug, ['completed','closed','cancelled']) ? 'text-red-500' : '' }}">
                    {{ $project->deadline?->format('Y-m-d') ?? '-' }}
                </span>
            </div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('project.budget') }}</span><span class="font-medium">{{ number_format($project->budget, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('project.payment_status') }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $project->payment_status === 'paid' ? 'bg-green-100 text-green-700' :
                       ($project->payment_status === 'partially_paid' ? 'bg-yellow-100 text-yellow-700' :
                       ($project->payment_status === 'overdue' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) }}">
                    {{ __('finance.'.$project->payment_status) }}
                </span>
            </div>
        </div>

        {{-- Team Members --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 mb-3 text-sm">{{ __('project.team_members') }}</h3>
            @foreach($project->teamMembers as $member)
            <div class="flex items-center gap-2 py-1.5">
                <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-600">
                    {{ mb_substr($member->name, 0, 1) }}
                </div>
                <span class="text-sm text-gray-700">{{ $member->name }}</span>
            </div>
            @endforeach
        </div>

        {{-- Workflow Steps --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 mb-3 text-sm flex items-center gap-2"><i class="fas fa-sitemap text-purple-500"></i> {{ __('project.workflow') }}</h3>
            @foreach($project->workflowSteps()->orderBy('step_order')->get() as $step)
            <div class="flex items-center gap-3 py-2">
                @if($step->completed)
                    <i class="fas fa-check-circle text-green-500 text-sm"></i>
                @else
                    <i class="far fa-circle text-gray-300 text-sm"></i>
                @endif
                <span class="text-sm {{ $step->completed ? 'text-gray-400 line-through' : 'text-gray-700' }}">{{ $step->step_name }}</span>
                @if(!$step->completed)
                <form action="{{ route('projects.workflow.complete', [$project, $step]) }}" method="POST" class="ms-auto">
                    @csrf
                    <button type="submit" class="text-xs text-blue-600 hover:underline">✓</button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
