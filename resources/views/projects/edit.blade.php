@extends('layouts.app')
@section('title', __('project.edit_project'))
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('project.edit_project') }}: {{ $project->name }}</h1>
    <a href="{{ route('projects.show', $project) }}" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl transition text-sm">
        <i class="fas fa-arrow-right"></i> {{ __('messages.back') }}
    </a>
</div>
@include('layouts.components.alert')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <form action="{{ route('projects.update', $project) }}" method="POST">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.name') }} *</label>
                <input type="text" name="name" value="{{ old('name', $project->name) }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.client') }} *</label>
                <select name="client_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id',$project->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.service_type') }} *</label>
                <select name="service_type_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($serviceTypes as $st)
                        <option value="{{ $st->id }}" {{ old('service_type_id',$project->service_type_id) == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.status') }} *</label>
                <select name="status_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($statuses as $s)
                        <option value="{{ $s->id }}" {{ old('status_id',$project->status_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.priority') }} *</label>
                <select name="priority" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['low','medium','high','urgent'] as $p)
                        <option value="{{ $p }}" {{ old('priority',$project->priority) === $p ? 'selected' : '' }}>{{ __('project.priority_'.$p) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.manager') }} *</label>
                <select name="manager_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($managers as $m)
                        <option value="{{ $m->id }}" {{ old('manager_id',$project->manager_id) == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.budget') }}</label>
                <input type="number" name="budget" value="{{ old('budget',$project->budget) }}" step="0.01"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.start_date') }}</label>
                <input type="date" name="start_date" value="{{ old('start_date',$project->start_date?->toDateString()) }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.deadline') }}</label>
                <input type="date" name="deadline" value="{{ old('deadline',$project->deadline?->toDateString()) }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.progress') }}</label>
                <input type="number" name="progress_percentage" value="{{ old('progress_percentage',$project->progress_percentage) }}" min="0" max="100"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.team_members') }}</label>
                <select name="team_members[]" multiple class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 h-28">
                    @foreach($teamMembers as $m)
                        <option value="{{ $m->id }}" {{ in_array($m->id, $selectedTeam ?? []) ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.description') }}</label>
                <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description',$project->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.internal_notes') }}</label>
                <textarea name="internal_notes" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('internal_notes',$project->internal_notes) }}</textarea>
            </div>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
                <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
            </button>
            <a href="{{ route('projects.show', $project) }}" class="bg-gray-100 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
