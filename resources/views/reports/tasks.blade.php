@extends('layouts.app')
@section('title', __('messages.tasks_report'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.tasks_report') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ __('messages.by_employee_status') }}</p>
    </div>
    <a href="{{ route('reports.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => __('task.total'), 'val' => $stats['total'],   'color' => 'blue'],
        ['label' => __('task.overdue'),   'val' => $stats['overdue'], 'color' => 'red'],
        ['label' => __('task.completed'),   'val' => $stats['done'],    'color' => 'green'],
        ['label' => __('task.blocked_tasks'),   'val' => $stats['blocked'], 'color' => 'orange'],
    ] as $c)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-{{ $c['color'] }}-100 flex items-center justify-center">
            <i class="fas fa-tasks text-{{ $c['color'] }}-600"></i>
        </div>
        <div><p class="text-2xl font-bold text-gray-800">{{ $c['val'] }}</p><p class="text-xs text-gray-500">{{ $c['label'] }}</p></div>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <select name="assigned_to" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('messages.employee') }}</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('assigned_to') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>
        <select name="status_id" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('messages.status') }}</option>
            @foreach($statuses as $s)
                <option value="{{ $s->id }}" {{ request('status_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm transition"><i class="fas fa-search me-1"></i>{{ __('messages.filter') }}</button>
        <a href="{{ route('reports.tasks') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-xl text-sm transition">{{ __('messages.reset') }}</a>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 border-b">
            <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('task.task') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">{{ __('project.project') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('task.assigned_to_label') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600">{{ __('task.status') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">{{ __('task.due_date') }}</th>
            <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">{{ __('task.hours') }}</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($tasks as $task)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3">
                    <a href="{{ route('tasks.show', $task) }}" class="font-medium text-gray-800 hover:text-blue-600">{{ $task->title }}</a>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-gray-500">{{ $task->project?->name }}</td>
                <td class="px-4 py-3 hidden lg:table-cell text-gray-600">{{ $task->assignedUser?->name ?? '-' }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium" style="background:{{ $task->status?->color }}20;color:{{ $task->status?->color }}">
                        {{ $task->status?->name }}
                    </span>
                </td>
                <td class="px-4 py-3 hidden xl:table-cell">
                    <span class="{{ $task->due_date?->isPast() && $task->status?->slug !== 'done' ? 'text-red-500 font-medium' : 'text-gray-500' }}">
                        {{ $task->due_date?->format('Y-m-d') ?? '-' }}
                    </span>
                </td>
                <td class="px-4 py-3 hidden xl:table-cell text-gray-500">{{ $task->actual_hours }}h / {{ $task->estimated_hours ?? '?' }}h</td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-12 text-center text-gray-400">{{ __('messages.no_tasks_found') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($tasks->hasPages())
    <div class="px-5 py-4 border-t">{{ $tasks->links() }}</div>
    @endif
</div>
@endsection
