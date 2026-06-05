@extends('layouts.app')
@section('title', $task->title)

@section('content')
<div class="mb-5 flex flex-wrap items-start justify-between gap-3">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('projects.show', $task->project) }}" class="text-sm text-blue-600 hover:underline">{{ $task->project->name }}</a>
            <span class="text-gray-300">/</span>
            <span class="text-sm text-gray-500">{{ __('task.tasks') }}</span>
        </div>
        <h1 class="text-xl font-bold text-gray-800">{{ $task->title }}</h1>
    </div>
    <div class="flex gap-2">
        @can('update', $task)
        <a href="{{ route('tasks.edit', $task) }}" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl transition text-sm">
            <i class="fas fa-edit"></i> {{ __('messages.edit') }}
        </a>
        @endcan
        <a href="{{ route('tasks.index') }}" class="flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-xl transition text-sm">
            <i class="fas fa-arrow-right"></i> {{ __('messages.back') }}
        </a>
    </div>
</div>

@include('layouts.components.alert')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-5">

        {{-- Description --}}
        @if($task->description)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 mb-3">{{ __('messages.description') }}</h3>
            <p class="text-gray-600 text-sm leading-relaxed">{{ $task->description }}</p>
        </div>
        @endif

        {{-- Checklist --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2"><i class="fas fa-check-square text-green-500"></i> {{ __('task.checklist') }}</h3>
            <form action="{{ route('tasks.checklist.store', $task) }}" method="POST" class="flex gap-2 mb-3">
                @csrf
                <input type="text" name="item" placeholder="{{ __('task.add_checklist_item') }}..."
                       class="flex-1 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-plus"></i></button>
            </form>
            @php $checklist = $task->checklists; $done = $checklist->where('is_completed',true)->count(); @endphp
            @if($checklist->count())
            <div class="text-xs text-gray-400 mb-2">{{ $done }}/{{ $checklist->count() }} {{ __('task.checklist') }}</div>
            @endif
            @foreach($checklist as $item)
            <div class="flex items-center gap-3 py-2 border-b last:border-0">
                <form action="{{ route('tasks.checklist.update', $item) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="is_completed" value="{{ $item->is_completed ? '0' : '1' }}">
                    <button type="submit" class="{{ $item->is_completed ? 'text-green-500' : 'text-gray-300' }} text-lg hover:text-green-600">
                        <i class="fas fa-{{ $item->is_completed ? 'check-circle' : 'circle' }}"></i>
                    </button>
                </form>
                <span class="text-sm flex-1 {{ $item->is_completed ? 'line-through text-gray-400' : 'text-gray-700' }}">{{ $item->item }}</span>
                <form action="{{ route('tasks.checklist.destroy', $item) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-300 hover:text-red-500 text-sm"><i class="fas fa-times"></i></button>
                </form>
            </div>
            @endforeach
        </div>

        {{-- Time Logs --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2"><i class="fas fa-clock text-orange-500"></i> {{ __('task.time_tracking') }}</h3>
            <form action="{{ route('tasks.time-logs.store', $task) }}" method="POST" class="grid grid-cols-3 gap-2 mb-3">
                @csrf
                <input type="number" name="hours" placeholder="{{ __('task.hours') }}" step="0.25" min="0.25" required
                       class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="date" name="logged_date" value="{{ now()->toDateString() }}" required
                       class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-2 rounded-xl text-sm transition">
                    <i class="fas fa-plus me-1"></i>{{ __('task.log_time') }}
                </button>
            </form>
            <p class="text-xs text-gray-500 mb-3">{{ __('task.actual_hours') }}: <strong>{{ $task->actual_hours }}h</strong> / {{ __('task.estimated_hours') }}: <strong>{{ $task->estimated_hours ?? '-' }}h</strong></p>
            @foreach($task->timeLogs as $log)
            <div class="flex items-center justify-between py-2 border-b last:border-0 text-sm">
                <div>
                    <span class="font-medium text-gray-700">{{ $log->hours }}h</span>
                    <span class="text-gray-400 ms-2">{{ $log->user->name }} · {{ $log->logged_date->format('d/m/Y') }}</span>
                    @if($log->description)<span class="text-gray-400 ms-2">— {{ $log->description }}</span>@endif
                </div>
                <form action="{{ route('tasks.time-logs.destroy', $log) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-300 hover:text-red-500"><i class="fas fa-times"></i></button>
                </form>
            </div>
            @endforeach
        </div>

        {{-- Comments --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2"><i class="fas fa-comments text-purple-500"></i> {{ __('task.comments') }}</h3>
            <form action="{{ route('tasks.comments.store', $task) }}" method="POST" class="mb-4">
                @csrf
                <textarea name="comment" rows="2" placeholder="{{ __('task.add_comment') }}..." required
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2"></textarea>
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-xl transition">
                    <i class="fas fa-paper-plane me-1"></i>{{ __('messages.save') }}
                </button>
            </form>
            @forelse($task->comments as $comment)
            <div class="flex gap-3 py-3 border-t">
                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0 text-xs font-bold text-purple-600">
                    {{ mb_substr($comment->user->name, 0, 1) }}
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-medium text-gray-800">{{ $comment->user->name }}</span>
                        <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-gray-700">{{ $comment->comment }}</p>
                </div>
                @if(auth()->id() === $comment->user_id)
                <form action="{{ route('tasks.comments.destroy', $comment) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-300 hover:text-red-500 text-sm"><i class="fas fa-trash"></i></button>
                </form>
                @endif
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4">{{ __('task.no_tasks') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-sm space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-500">{{ __('task.status') }}</span>
                <span class="px-2 py-1 rounded-full text-xs font-medium" style="background:{{ $task->status->color }}20; color:{{ $task->status->color }}">{{ $task->status->name }}</span>
            </div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('project.priority') }}</span><span class="font-medium">{{ __('project.priority_'.$task->priority) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('task.assigned_to') }}</span><span class="font-medium">{{ $task->assignedUser?->name ?? '-' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('task.start_date') }}</span><span class="font-medium">{{ $task->start_date?->format('Y-m-d') ?? '-' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('task.due_date') }}</span>
                <span class="font-medium {{ $task->due_date?->isPast() ? 'text-red-500' : '' }}">{{ $task->due_date?->format('Y-m-d') ?? '-' }}</span>
            </div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('task.progress') }}</span><span class="font-medium">{{ $task->progress_percentage }}%</span></div>
        </div>
    </div>
</div>
@endsection
