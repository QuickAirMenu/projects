@extends('layouts.app')
@section('title', __('expenses.recurring_title'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('expenses.recurring_title') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('expenses.recurring_hint') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('expenses.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm">{{ __('messages.back') }}</a>
        <a href="{{ route('recurring-expenses.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium">
            <i class="fas fa-plus me-2"></i>{{ __('expenses.recurring_new') }}
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('expenses.recurring_active_count') }}</p>
        <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('expenses.recurring_due') }}</p>
        <p class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['due'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('expenses.recurring_monthly_value') }}</p>
        <p class="mt-2 text-2xl font-bold text-gray-800">{{ number_format((float) $stats['monthly'], 2) }}</p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
            <tr>
                <th class="px-4 py-3 text-start">{{ __('expenses.vendor') }}</th>
                <th class="px-4 py-3 text-start">{{ __('expenses.category') }}</th>
                <th class="px-4 py-3 text-end">{{ __('expenses.total') }}</th>
                <th class="px-4 py-3 text-start">{{ __('expenses.recurring_frequency') }}</th>
                <th class="px-4 py-3 text-start">{{ __('expenses.next_run') }}</th>
                <th class="px-4 py-3 text-start">{{ __('expenses.last_run') }}</th>
                <th class="px-4 py-3 text-start">{{ __('time.status') }}</th>
                <th class="px-4 py-3 text-end">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($recurring as $item)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-800">{{ $item->vendor_name }}</p>
                    <p class="text-xs text-gray-400">{{ $item->project?->name ?? $item->client?->name }}</p>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ __('expenses.cat_'.$item->category) }}</td>
                <td class="px-4 py-3 text-end">{{ number_format($item->total(), 2) }}</td>
                <td class="px-4 py-3 text-gray-600">{{ __('expenses.freq_'.$item->frequency) }}</td>
                <td class="px-4 py-3 {{ $item->is_active && $item->next_run_date?->isPast() ? 'text-amber-600 font-semibold' : 'text-gray-600' }}">{{ $item->next_run_date?->format('Y-m-d') }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $item->last_run_at?->format('Y-m-d H:i') ?? '-' }}</td>
                <td class="px-4 py-3">
                    @if($item->is_active)
                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs text-emerald-700">{{ __('expenses.recurring_active') }}</span>
                    @else
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs text-gray-500">{{ __('expenses.recurring_inactive') }}</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-end whitespace-nowrap">
                    <form action="{{ route('recurring-expenses.run', $item) }}" method="POST" class="inline">
                        @csrf
                        <button class="text-emerald-600 hover:text-emerald-800 text-xs me-2"><i class="fas fa-bolt me-1"></i>{{ __('messages.run_now') }}</button>
                    </form>
                    <a href="{{ route('recurring-expenses.edit', $item) }}" class="text-blue-600 hover:text-blue-800 text-xs me-2"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('recurring-expenses.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                        @csrf @method('DELETE')
                        <button class="text-red-500 hover:text-red-700 text-xs"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">{{ __('messages.no_data') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $recurring->links() }}</div>
@endsection
