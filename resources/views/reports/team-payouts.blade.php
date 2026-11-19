@extends('layouts.app')
@section('title', __('reports.team_payouts_title'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('reports.team_payouts_title') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('reports.team_payouts_hint') }}</p>
    </div>
    <a href="{{ route('reports.index') }}" class="rounded-xl bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs text-gray-500">{{ __('reports.team_unpaid') }}</p>
        <p class="mt-2 text-2xl font-bold text-red-600">{{ number_format((float) $totals['unpaid'], 2) }}</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs text-gray-500">{{ __('reports.team_paid') }}</p>
        <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format((float) $totals['paid'], 2) }}</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs text-gray-500">{{ __('reports.team_tasks') }}</p>
        <p class="mt-2 text-2xl font-bold text-gray-800">{{ $totals['count'] }}</p>
    </div>
</div>

<form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 flex flex-wrap gap-3">
    <select name="user_id" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
        <option value="">{{ __('reports.all_members') }}</option>
        @foreach($users as $user)
        <option value="{{ $user->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
        @endforeach
    </select>
    <select name="project_id" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
        <option value="">{{ __('time.all_projects') }}</option>
        @foreach($projects as $project)
        <option value="{{ $project->id }}" @selected((string) ($filters['project_id'] ?? '') === (string) $project->id)>{{ $project->name }}</option>
        @endforeach
    </select>
    <select name="status" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
        <option value="unpaid" @selected(($filters['status'] ?? 'unpaid') === 'unpaid')>{{ __('reports.team_unpaid') }}</option>
        <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>{{ __('reports.team_paid') }}</option>
        <option value="all" @selected(($filters['status'] ?? '') === 'all')>{{ __('reports.team_all') }}</option>
    </select>
    <button class="rounded-xl bg-gray-800 px-4 py-2 text-sm text-white">{{ __('messages.filter') }}</button>
</form>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
            <tr>
                <th class="px-4 py-3 text-start">{{ __('reports.team_member') }}</th>
                <th class="px-4 py-3 text-start">{{ __('time.task') }}</th>
                <th class="px-4 py-3 text-start">{{ __('common.project') }}</th>
                <th class="px-4 py-3 text-end">{{ __('task.team_cost') }}</th>
                <th class="px-4 py-3 text-start">{{ __('reports.team_status') }}</th>
                <th class="px-4 py-3 text-end">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($tasks as $task)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-800">{{ $task->assignedUser?->name ?? '-' }}</td>
                <td class="px-4 py-3"><a href="{{ route('tasks.show', $task) }}" class="text-gray-700 hover:text-blue-600">{{ $task->title }}</a></td>
                <td class="px-4 py-3 text-gray-600">{{ $task->project?->name ?? '-' }}</td>
                <td class="px-4 py-3 text-end font-medium">{{ number_format((float) $task->cost_amount, 2) }}</td>
                <td class="px-4 py-3">
                    @if($task->cost_paid_at)
                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs text-emerald-700">{{ __('task.cost_paid') }}</span>
                    @else
                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs text-amber-700">{{ __('task.cost_unpaid') }}</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-end">
                    @if($task->cost_paid_at)
                    <form action="{{ route('tasks.cost.unpay', $task) }}" method="POST">
                        @csrf
                        <button class="text-xs text-gray-500 hover:text-gray-700">{{ __('task.mark_unpaid') }}</button>
                    </form>
                    @else
                    <form action="{{ route('tasks.cost.pay', $task) }}" method="POST">
                        @csrf
                        <button class="text-xs text-emerald-600 hover:text-emerald-800"><i class="fas fa-check me-1"></i>{{ __('task.mark_paid') }}</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">{{ __('messages.no_data') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $tasks->links() }}</div>
@endsection
