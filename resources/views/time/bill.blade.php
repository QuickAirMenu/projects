@extends('layouts.app')
@section('title', __('time.bill_invoice'))

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('time-entries.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm"><i class="fas fa-arrow-{{ is_rtl() ? 'right' : 'left' }}"></i></a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('time.bill_invoice') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('time.bill_hint') }}</p>
    </div>
</div>

<form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 grid grid-cols-2 md:grid-cols-4 gap-3">
    <select name="project_id" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
        <option value="">{{ __('time.all_projects') }}</option>
        @foreach($projects as $project)
        <option value="{{ $project->id }}" @selected($projectId == $project->id)>{{ $project->name }}</option>
        @endforeach
    </select>
    <input type="date" name="from" value="{{ $from }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
    <input type="date" name="to" value="{{ $to }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
    <button class="rounded-xl bg-gray-800 px-4 py-2 text-sm text-white">{{ __('time.preview') }}</button>
</form>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('time.total_hours') }}</p>
        <p class="mt-2 text-2xl font-bold text-gray-800">{{ number_format($totalHours, 2) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('time.total_amount') }}</p>
        <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($totalAmount, 2) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('time.entries_count') }}</p>
        <p class="mt-2 text-2xl font-bold text-gray-800">{{ $entries->count() }}</p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto mb-5">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
            <tr>
                <th class="px-4 py-3 text-start">{{ __('time.work_date') }}</th>
                <th class="px-4 py-3 text-start">{{ __('common.project') }}</th>
                <th class="px-4 py-3 text-start">{{ __('time.description') }}</th>
                <th class="px-4 py-3 text-end">{{ __('time.hours') }}</th>
                <th class="px-4 py-3 text-end">{{ __('time.amount') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($entries as $entry)
            <tr>
                <td class="px-4 py-3 text-gray-700">{{ $entry->work_date?->format('Y-m-d') }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $entry->project?->name }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $entry->description }}</td>
                <td class="px-4 py-3 text-end">{{ number_format((float) $entry->hours, 2) }}</td>
                <td class="px-4 py-3 text-end">{{ number_format($entry->amount(), 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">{{ __('time.no_unbilled') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($entries->isNotEmpty())
<form action="{{ route('time-entries.bill.store') }}" method="POST" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
    @csrf
    <input type="hidden" name="project_id" value="{{ $entries->first()->project_id }}">
    <input type="hidden" name="from" value="{{ $from }}">
    <input type="hidden" name="to" value="{{ $to }}">
    <div class="md:col-span-2">
        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('common.project') }}</label>
        <p class="rounded-xl bg-gray-50 px-3 py-2.5 text-sm">{{ $entries->first()->project?->name }}</p>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('time.tax_percentage') }}</label>
        <input type="number" name="tax_percentage" value="15" min="0" max="100" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('time.due_date') }}</label>
        <input type="date" name="due_date" value="{{ now()->addDays(15)->toDateString() }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
    </div>
    <button class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700">
        <i class="fas fa-file-invoice-dollar me-2"></i>{{ __('time.generate_invoice') }}
    </button>
</form>
@endif
@endsection
