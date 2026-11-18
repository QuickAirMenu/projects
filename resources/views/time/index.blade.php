@extends('layouts.app')
@section('title', __('time.timesheets'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('time.timesheets') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('time.hint') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('time-entries.bill.preview') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium">
            <i class="fas fa-file-invoice-dollar me-2"></i>{{ __('time.bill_invoice') }}
        </a>
        <a href="{{ route('time-entries.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium">
            <i class="fas fa-plus me-2"></i>{{ __('time.new_entry') }}
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('time.total_hours') }}</p>
        <p class="mt-2 text-2xl font-bold text-gray-800">{{ number_format((float) ($totals->hours ?? 0), 2) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('time.total_amount') }}</p>
        <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format((float) ($totals->amount ?? 0), 2) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('time.unbilled') }}</p>
        <p class="mt-2 text-2xl font-bold text-amber-600">{{ \App\Models\TimeEntry::unbilled()->count() }}</p>
    </div>
</div>

<form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 grid grid-cols-2 md:grid-cols-6 gap-3">
    <select name="project_id" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
        <option value="">{{ __('time.all_projects') }}</option>
        @foreach($projects as $project)
        <option value="{{ $project->id }}" @selected(($filters['project_id'] ?? '') == $project->id)>{{ $project->name }}</option>
        @endforeach
    </select>
    <select name="user_id" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
        <option value="">{{ __('time.all_users') }}</option>
        @foreach($users as $user)
        <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>{{ $user->name }}</option>
        @endforeach
    </select>
    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
    <label class="flex items-center gap-2 text-sm text-gray-600 px-2">
        <input type="checkbox" name="unbilled" value="1" @checked($filters['unbilled'] ?? false) class="rounded border-gray-300">
        {{ __('time.unbilled_only') }}
    </label>
    <button class="rounded-xl bg-gray-800 px-4 py-2 text-sm text-white">{{ __('time.apply') }}</button>
</form>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
            <tr>
                <th class="px-4 py-3 text-start">{{ __('time.work_date') }}</th>
                <th class="px-4 py-3 text-start">{{ __('time.worker') }}</th>
                <th class="px-4 py-3 text-start">{{ __('common.project') }}</th>
                <th class="px-4 py-3 text-start">{{ __('time.description') }}</th>
                <th class="px-4 py-3 text-end">{{ __('time.hours') }}</th>
                <th class="px-4 py-3 text-end">{{ __('time.hourly_rate') }}</th>
                <th class="px-4 py-3 text-end">{{ __('time.amount') }}</th>
                <th class="px-4 py-3 text-start">{{ __('time.status') }}</th>
                <th class="px-4 py-3 text-end">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($entries as $entry)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-700">{{ $entry->work_date?->format('Y-m-d') }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $entry->user?->name }}</td>
                <td class="px-4 py-3">
                    <p class="text-gray-700">{{ $entry->project?->name }}</p>
                    @if($entry->task)<p class="text-xs text-gray-400">{{ $entry->task->title }}</p>@endif
                </td>
                <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $entry->description }}</td>
                <td class="px-4 py-3 text-end">{{ number_format((float) $entry->hours, 2) }}</td>
                <td class="px-4 py-3 text-end">{{ number_format((float) $entry->hourly_rate, 2) }}</td>
                <td class="px-4 py-3 text-end font-medium">{{ number_format($entry->amount(), 2) }}</td>
                <td class="px-4 py-3">
                    @if($entry->isInvoiced())
                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs text-emerald-700">{{ __('time.invoiced') }}</span>
                    @else
                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs text-amber-700">{{ __('time.not_invoiced') }}</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-end whitespace-nowrap">
                    @unless($entry->isInvoiced())
                    <a href="{{ route('time-entries.edit', $entry) }}" class="text-blue-600 hover:text-blue-800 text-xs me-2"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('time-entries.destroy', $entry) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                        @csrf @method('DELETE')
                        <button class="text-red-500 hover:text-red-700 text-xs"><i class="fas fa-trash"></i></button>
                    </form>
                    @endunless
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-4 py-10 text-center text-gray-400">{{ __('time.no_entries') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $entries->links() }}</div>
@endsection
