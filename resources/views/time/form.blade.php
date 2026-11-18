@extends('layouts.app')
@section('title', $entry->exists ? __('time.edit_entry') : __('time.new_entry'))

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('time-entries.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm"><i class="fas fa-arrow-{{ is_rtl() ? 'right' : 'left' }}"></i></a>
    <h1 class="text-2xl font-bold text-gray-800">{{ $entry->exists ? __('time.edit_entry') : __('time.new_entry') }}</h1>
</div>

<form action="{{ $entry->exists ? route('time-entries.update', $entry) : route('time-entries.store') }}" method="POST" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
    @csrf
    @if($entry->exists) @method('PUT') @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('common.project') }}</label>
            <select name="project_id" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                <option value="">{{ __('time.select_project') }}</option>
                @foreach($projects as $project)
                <option value="{{ $project->id }}" @selected(old('project_id', $entry->project_id) == $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('time.task') }}</label>
            <select name="task_id" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                <option value="">{{ __('time.no_task') }}</option>
                @foreach($tasks as $task)
                <option value="{{ $task->id }}" @selected(old('task_id', $entry->task_id) == $task->id)>{{ $task->title }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('time.worker') }}</label>
            <select name="user_id" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(old('user_id', $entry->user_id ?? auth()->id()) == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('time.work_date') }}</label>
            <input type="date" name="work_date" required value="{{ old('work_date', $entry->work_date?->format('Y-m-d')) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('time.hours') }}</label>
            <input type="number" step="0.25" min="0.25" max="24" name="hours" required value="{{ old('hours', (float) $entry->hours) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('time.hourly_rate') }}</label>
            <input type="number" step="0.01" min="0" name="hourly_rate" required value="{{ old('hourly_rate', (float) $entry->hourly_rate) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('time.description') }}</label>
            <input name="description" required maxlength="500" value="{{ old('description', $entry->description) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div class="md:col-span-3">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="billable" value="1" @checked(old('billable', $entry->billable ?? true)) class="rounded border-gray-300">
                {{ __('time.billable') }}
            </label>
        </div>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('time-entries.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm">{{ __('messages.cancel') }}</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium">{{ __('messages.save') }}</button>
    </div>
</form>
@endsection
