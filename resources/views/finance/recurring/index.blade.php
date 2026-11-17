@extends('layouts.app')
@section('title', __('finance.recurring_invoices'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('finance.recurring_invoices') }}</h1>
    <a href="{{ route('recurring-invoices.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium">
        <i class="fas fa-plus me-2"></i>{{ __('messages.new_recurring_invoice') }}
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('messages.recurring_total') }}</p>
        <p class="mt-2 text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('finance.active') }}</p>
        <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('messages.due_now') }}</p>
        <p class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['due'] }}</p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
            <tr>
                <th class="px-5 py-3 text-start">{{ __('finance.name') }}</th>
                <th class="px-5 py-3 text-start">{{ __('common.client') }}</th>
                <th class="px-5 py-3 text-start">{{ __('finance.frequency') }}</th>
                <th class="px-5 py-3 text-start">{{ __('finance.next_run') }}</th>
                <th class="px-5 py-3 text-start">{{ __('finance.last_run') }}</th>
                <th class="px-5 py-3 text-start">{{ __('messages.status') }}</th>
                <th class="px-5 py-3 text-end">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($recurring as $item)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3">
                    <p class="font-medium text-gray-800">{{ $item->name }}</p>
                    <p class="text-xs text-gray-400">{{ $item->project?->name }}</p>
                </td>
                <td class="px-5 py-3 text-gray-600">{{ $item->client?->name }}</td>
                <td class="px-5 py-3 text-gray-600">{{ __('finance.freq_'.$item->frequency) }}</td>
                <td class="px-5 py-3 {{ $item->is_active && $item->next_run_date?->isPast() ? 'text-amber-600 font-semibold' : 'text-gray-600' }}">
                    {{ $item->next_run_date?->format('Y-m-d') }}
                </td>
                <td class="px-5 py-3 text-gray-500">{{ $item->last_run_at?->format('Y-m-d H:i') ?? '-' }}</td>
                <td class="px-5 py-3">
                    @if($item->is_active)
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs text-emerald-700">{{ __('finance.active') }}</span>
                    @else
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-500">{{ __('finance.inactive') }}</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-end whitespace-nowrap">
                    <form action="{{ route('recurring-invoices.run', $item) }}" method="POST" class="inline">
                        @csrf
                        <button class="text-emerald-600 hover:text-emerald-800 text-xs me-2"><i class="fas fa-bolt me-1"></i>{{ __('messages.run_now') }}</button>
                    </form>
                    <a href="{{ route('recurring-invoices.edit', $item) }}" class="text-blue-600 hover:text-blue-800 text-xs me-2"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('recurring-invoices.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                        @csrf @method('DELETE')
                        <button class="text-red-500 hover:text-red-700 text-xs"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">{{ __('messages.no_data') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $recurring->links() }}</div>

<p class="mt-4 text-xs text-gray-400">
    <i class="fas fa-clock me-1"></i>{{ __('messages.recurring_cron_hint') }}
</p>
@endsection
