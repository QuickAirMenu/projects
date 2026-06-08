@extends('layouts.app')
@section('title', __('task.edit_task') ?? 'Edit Task')
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ $task->title }}</h1>
    <a href="{{ route('tasks.show', $task) }}" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl transition text-sm">
        <i class="fas fa-arrow-right"></i> {{ __('messages.back') }}
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <form action="{{ route('tasks.update', $task) }}" method="POST">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('task.task') }} *</label>
                <input type="text" name="title" value="{{ old('title',$task->title) }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('task.status') }} *</label>
                <select name="status_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($statuses as $s)
                        <option value="{{ $s->id }}" {{ old('status_id',$task->status_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.priority') }} *</label>
                <select name="priority" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['low','medium','high','urgent'] as $p)
                        <option value="{{ $p }}" {{ old('priority',$task->priority) === $p ? 'selected' : '' }}>{{ __('project.priority_'.$p) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('task.assigned_to') }}</label>
                <select name="assigned_to" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- {{ __('messages.select') }} --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ old('assigned_to',$task->assigned_to) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('task.progress') }}</label>
                <input type="number" name="progress_percentage" value="{{ old('progress_percentage',$task->progress_percentage) }}" min="0" max="100"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('task.start_date') }}</label>
                <input type="date" name="start_date" value="{{ old('start_date',$task->start_date?->toDateString()) }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('task.due_date') }}</label>
                <input type="date" name="due_date" value="{{ old('due_date',$task->due_date?->toDateString()) }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('task.estimated_hours') }}</label>
                <input type="number" name="estimated_hours" value="{{ old('estimated_hours',$task->estimated_hours) }}" step="0.5"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.description') }}</label>
                <textarea name="description" rows="4" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description',$task->description) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_blocked" value="1" {{ old('is_blocked',$task->is_blocked) ? 'checked' : '' }} class="w-4 h-4 rounded text-red-500">
                    <span class="text-sm font-medium text-gray-700">{{ __('task.is_blocked') }}</span>
                </label>
                <textarea name="blocked_reason" rows="2" placeholder="{{ __('task.blocked_reason') }}"
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 mt-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('blocked_reason',$task->blocked_reason) }}</textarea>
            </div>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
                <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
            </button>
            <a href="{{ route('tasks.show', $task) }}" class="bg-gray-100 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
