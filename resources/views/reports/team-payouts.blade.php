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

@can('create-payments')
@if(($accounts ?? collect())->isNotEmpty())
<form id="batchPayoutForm" action="{{ route('reports.team-payouts.pay') }}" method="POST" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 flex flex-wrap items-end gap-3">
    @csrf
    <div class="min-w-48">
        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('reports.payout_select_account') }}</label>
        <select name="account_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            @foreach($accounts as $account)
            <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->currency }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('messages.date') }}</label>
        <input type="date" name="payout_date" value="{{ now()->toDateString() }}" required class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
    </div>
    <div class="flex-1 min-w-48">
        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('reports.payout_note') }}</label>
        <input name="note" maxlength="300" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
    </div>
    <div class="text-xs text-gray-500 px-1">
        <span id="payoutSelectedCount">0</span> {{ __('reports.team_tasks') }} ·
        <span id="payoutSelectedBase">0.00</span>
    </div>
    <button type="submit" id="payoutSubmit" disabled class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white disabled:opacity-40">
        <i class="fas fa-money-bill-transfer me-2"></i>{{ __('reports.payout_batch') }}
    </button>
</form>
@endif
@endcan

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
            <tr>
                <th class="px-3 py-3 text-start w-10"><input type="checkbox" id="payoutSelectAll" class="rounded border-gray-300"></th>
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
                <td class="px-3 py-3">
                    @unless($task->cost_paid_at)
                    <input type="checkbox" form="batchPayoutForm" name="task_ids[]" value="{{ $task->id }}" class="payout-task rounded border-gray-300" data-base="{{ (float) $task->cost_amount * (float) ($task->cost_exchange_rate ?: 1) }}">
                    @endunless
                </td>
                <td class="px-4 py-3 text-gray-800">
                    {{ $task->assignedUser?->name ?? '-' }}
                    @if($task->assignedUser)
                    <a href="{{ route('reports.team-payouts.statement', ['user' => $task->assignedUser->id, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}" class="ms-1 text-blue-600 text-xs" title="{{ __('reports.payout_statement') }}"><i class="fas fa-file-pdf"></i></a>
                    @endif
                </td>
                <td class="px-4 py-3"><a href="{{ route('tasks.show', $task) }}" class="text-gray-700 hover:text-blue-600">{{ $task->title }}</a></td>
                <td class="px-4 py-3 text-gray-600">{{ $task->project?->name ?? '-' }}</td>
                <td class="px-4 py-3 text-end font-medium">{{ number_format((float) $task->cost_amount, 2) }} {{ $task->cost_currency ?: '' }}<span class="block text-[11px] text-gray-400">≈ {{ number_format((float) $task->cost_amount * (float) ($task->cost_exchange_rate ?: 1), 2) }}</span></td>
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
                    <a href="{{ route('tasks.cost.payout', $task) }}" class="text-xs text-emerald-600 hover:text-emerald-800"><i class="fas fa-check me-1"></i>{{ __('task.mark_paid') }}</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">{{ __('messages.no_data') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $tasks->links() }}</div>

@push('scripts')
<script>
    (function () {
        const all = document.getElementById('payoutSelectAll');
        const submit = document.getElementById('payoutSubmit');
        const countEl = document.getElementById('payoutSelectedCount');
        const baseEl = document.getElementById('payoutSelectedBase');
        if (!all || !submit) return;

        function boxes() { return Array.from(document.querySelectorAll('.payout-task')); }
        function refresh() {
            const selected = boxes().filter(c => c.checked);
            const total = selected.reduce((sum, c) => sum + parseFloat(c.dataset.base || '0'), 0);
            countEl.textContent = selected.length;
            baseEl.textContent = total.toFixed(2);
            submit.disabled = selected.length === 0;
        }
        all.addEventListener('change', function () {
            boxes().forEach(c => { c.checked = all.checked; });
            refresh();
        });
        boxes().forEach(c => c.addEventListener('change', refresh));
        refresh();
    })();
</script>
@endpush
@endsection
