@extends('layouts.app')
@section('title', __('task.add_task'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('task.add_task') }}</h1>
    <a href="{{ route('tasks.index') }}" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl transition text-sm">
        <i class="fas fa-arrow-right"></i> {{ __('messages.back') }}
    </a>
</div>


<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <form action="{{ route('tasks.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('task.task') }} *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.project') }} *</label>
                <select name="project_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- {{ __('messages.select') }} --</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ old('project_id', request('project_id')) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
                @error('project_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('task.assigned_to') }}</label>
                <select name="assigned_to" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- {{ __('messages.select') }} --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ old('assigned_to') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
                @error('assigned_to')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('task.status') }} *</label>
                <select name="status_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($statuses as $s)
                        <option value="{{ $s->id }}" {{ old('status_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('status_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.priority') }} *</label>
                <select name="priority" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['low','medium','high','urgent'] as $p)
                        <option value="{{ $p }}" {{ old('priority','medium') === $p ? 'selected' : '' }}>{{ __('project.priority_'.$p) }}</option>
                    @endforeach
                </select>
                @error('priority')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('task.start_date') }}</label>
                <input type="date" name="start_date" value="{{ old('start_date') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('task.due_date') }}</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('due_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('task.estimated_hours') }}
                    <x-help :text="__('task.estimated_hours_help')" />
                </label>
                <input type="number" name="estimated_hours" value="{{ old('estimated_hours') }}" step="0.5" min="0"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('estimated_hours')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('task.team_cost') }}
                    <x-help :text="__('task.team_cost_help')" />
                </label>
                <input type="number" name="cost_amount" value="{{ old('cost_amount') }}" step="0.01" min="0"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('cost_amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('task.cost_currency') }}</label>
                    <select name="cost_currency" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        @foreach(['SAR','EGP','USD','AED'] as $cur)
                        <option value="{{ $cur }}" @selected(old('cost_currency', workspace_config('currency') ?: 'SAR') === $cur)>{{ $cur }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('task.cost_rate') }}</label>
                    <input type="number" step="0.000001" min="0.000001" name="cost_exchange_rate" value="{{ old('cost_exchange_rate', 1) }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.description') }}</label>
                <textarea name="description" rows="4"
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
                <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
            </button>
            <a href="{{ route('tasks.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition">
                {{ __('messages.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection
