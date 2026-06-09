@extends('layouts.app')
@section('title', __('task.tasks'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('task.tasks') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $stats['total'] }} {{ __('task.task') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('tasks.kanban') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
            <i class="fas fa-columns me-2"></i>{{ __('task.kanban') }}
        </a>
        <a href="{{ route('tasks.calendar') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
            <i class="fas fa-calendar me-2"></i>{{ __('task.calendar') }}
        </a>
        @can('create', App\Models\Task::class)
        <a href="{{ route('tasks.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm transition font-medium">
            <i class="fas fa-plus me-2"></i>{{ __('task.add_task') }}
        </a>
        @endcan
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => __('task.total'),   'val' => $stats['total'],   'color' => 'blue',   'icon' => 'fa-tasks'],
        ['label' => __('task.my_tasks'),       'val' => $stats['my_open'], 'color' => 'purple', 'icon' => 'fa-user-check'],
        ['label' => __('task.overdue'),      'val' => $stats['overdue'], 'color' => 'red',    'icon' => 'fa-clock'],
        ['label' => __('task.completed'),      'val' => $stats['done'],    'color' => 'green',  'icon' => 'fa-check-circle'],
    ] as $c)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-{{ $c['color'] }}-100 flex items-center justify-center">
            <i class="fas {{ $c['icon'] }} text-{{ $c['color'] }}-600 text-xl"></i>
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
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search') }}..."
               class="flex-1 min-w-48 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="status_id" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('task.status') }}</option>
            @foreach($statuses as $s)
                <option value="{{ $s->id }}" {{ request('status_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        <select name="assigned_to" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('task.assigned_to') }}</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('assigned_to') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>
        <select name="priority" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('project.priority') }}</option>
            @foreach(['low','medium','high','urgent'] as $p)
                <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ __('project.priority_'.$p) }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
            <input type="checkbox" name="overdue" value="1" {{ request('overdue') ? 'checked' : '' }} class="rounded">
            {{ __('task.overdue_only') }}
        </label>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm transition">
            <i class="fas fa-search me-1"></i>{{ __('messages.filter') }}
        </button>
        <a href="{{ route('tasks.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-xl text-sm transition">{{ __('messages.reset') }}</a>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('task.task') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">{{ __('project.project') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('task.status') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('project.priority') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">{{ __('task.assigned_to') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">{{ __('task.due_date') }}</th>
                <th class="text-end px-5 py-3 font-semibold text-gray-600">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($tasks as $task)
            <tr class="hover:bg-gray-50 transition {{ $task->is_blocked ? 'bg-red-50' : '' }}">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        @if($task->is_blocked)<span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>@endif
                        <a href="{{ route('tasks.show', $task) }}" class="font-medium text-gray-800 hover:text-blue-600">{{ $task->title }}</a>
                    </div>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $task->project?->name ?? '-' }}</td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    <span class="px-2 py-1 rounded-full text-xs font-medium" style="background:{{ $task->status?->color }}20; color:{{ $task->status?->color }}">
                        {{ $task->status?->name }}
                    </span>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    @php $pc = ['low'=>'blue','medium'=>'yellow','high'=>'orange','urgent'=>'red'][$task->priority] ?? 'gray' @endphp
                    <span class="px-2 py-1 rounded-full text-xs bg-{{ $pc }}-100 text-{{ $pc }}-700">{{ __('project.priority_'.$task->priority) }}</span>
                </td>
                <td class="px-4 py-3 hidden xl:table-cell text-gray-600">{{ $task->assignedUser?->name ?? '-' }}</td>
                <td class="px-4 py-3 hidden xl:table-cell">
                    <span class="{{ $task->due_date?->isPast() && $task->status?->slug !== 'done' ? 'text-red-500 font-medium' : 'text-gray-600' }}">
                        {{ $task->due_date?->format('Y-m-d') ?? '-' }}
                    </span>
                </td>
                <td class="px-5 py-3 text-end">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('tasks.show', $task) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"><i class="fas fa-eye"></i></a>
                        @can('update', $task)
                        <a href="{{ route('tasks.edit', $task) }}" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg transition"><i class="fas fa-edit"></i></a>
                        @endcan
                        @can('delete', $task)
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirmDelete(this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition"><i class="fas fa-trash"></i></button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="py-16 text-center text-gray-400">
                <i class="fas fa-tasks text-5xl mb-3 block text-gray-200"></i>
                {{ __('task.no_tasks') }}
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($tasks->hasPages())
    <div class="px-5 py-4 border-t">{{ $tasks->links() }}</div>
    @endif
</div>
@endsection
