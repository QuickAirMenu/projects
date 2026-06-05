@extends('layouts.app')
@section('title', __('task.kanban'))

@section('content')
<div class="mb-5 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('task.kanban') }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('tasks.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
            <i class="fas fa-list me-2"></i>{{ __('messages.view') }}
        </a>
        @can('create', App\Models\Task::class)
        <a href="{{ route('tasks.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm transition">
            <i class="fas fa-plus me-2"></i>{{ __('task.add_task') }}
        </a>
        @endcan
    </div>
</div>

<div class="flex gap-4 overflow-x-auto pb-4" id="kanban-board">
    @foreach($statuses as $status)
    <div class="flex-shrink-0 w-72 bg-gray-50 rounded-2xl p-4" data-status-id="{{ $status->id }}">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full" style="background:{{ $status->color }}"></div>
                <h3 class="font-semibold text-gray-700 text-sm">{{ $status->name }}</h3>
            </div>
            <span class="bg-white border text-xs text-gray-500 px-2 py-0.5 rounded-full">
                {{ isset($tasks[$status->id]) ? $tasks[$status->id]->count() : 0 }}
            </span>
        </div>

        <div class="space-y-3 min-h-24 kanban-column" data-status-id="{{ $status->id }}">
            @foreach($tasks[$status->id] ?? [] as $task)
            <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100 cursor-grab active:cursor-grabbing kanban-card"
                 draggable="true" data-task-id="{{ $task->id }}" data-status-id="{{ $status->id }}">
                <a href="{{ route('tasks.show', $task) }}" class="font-medium text-gray-800 text-sm hover:text-blue-600 block mb-2">{{ $task->title }}</a>
                <p class="text-xs text-gray-400 mb-2">{{ $task->project->name }}</p>
                <div class="flex items-center justify-between">
                    @php $pc = ['low'=>'blue','medium'=>'yellow','high'=>'orange','urgent'=>'red'][$task->priority] ?? 'gray' @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $pc }}-100 text-{{ $pc }}-700">{{ __('project.priority_'.$task->priority) }}</span>
                    @if($task->due_date)
                    <span class="text-xs {{ $task->due_date->isPast() ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                        <i class="fas fa-calendar-alt me-1"></i>{{ $task->due_date->format('d/m') }}
                    </span>
                    @endif
                </div>
                @if($task->assignedUser)
                <div class="mt-2 flex items-center gap-1">
                    <div class="w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-600">
                        {{ mb_substr($task->assignedUser->name, 0, 1) }}
                    </div>
                    <span class="text-xs text-gray-400">{{ $task->assignedUser->name }}</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script>
// Drag-and-drop Kanban
let dragged = null;
document.querySelectorAll('.kanban-card').forEach(card => {
    card.addEventListener('dragstart', () => { dragged = card; card.classList.add('opacity-50'); });
    card.addEventListener('dragend', () => { dragged = null; card.classList.remove('opacity-50'); });
});

document.querySelectorAll('.kanban-column').forEach(col => {
    col.addEventListener('dragover', e => { e.preventDefault(); col.classList.add('bg-blue-50'); });
    col.addEventListener('dragleave', () => col.classList.remove('bg-blue-50'));
    col.addEventListener('drop', async e => {
        e.preventDefault();
        col.classList.remove('bg-blue-50');
        if (!dragged) return;
        const taskId   = dragged.dataset.taskId;
        const statusId = col.dataset.statusId;
        col.appendChild(dragged);
        await fetch(`/tasks/${taskId}/status`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ status_id: statusId })
        });
    });
});
</script>
@endpush
@endsection
