@extends('layouts.app')
@section('title', __('expenses.title'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('expenses.title') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('expenses.hint') }}</p>
    </div>
    <div class="flex gap-2">
    <a href="{{ route('expenses.bill.preview') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium">
        <i class="fas fa-file-invoice-dollar me-2"></i>{{ __('expenses.bill_invoice') }}
    </a>
    <a href="{{ route('expenses.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium">
        <i class="fas fa-plus me-2"></i>{{ __('expenses.new') }}
    </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('expenses.pending_total') }}</p>
        <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format((float) $totals['pending'], 2) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('expenses.approved_total') }}</p>
        <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format((float) $totals['approved'], 2) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('expenses.entries') }}</p>
        <p class="mt-2 text-2xl font-bold text-gray-800">{{ $totals['count'] }}</p>
    </div>
</div>

<form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 grid grid-cols-2 md:grid-cols-6 gap-3">
    <select name="status" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
        <option value="">{{ __('expenses.all_statuses') }}</option>
        @foreach(['pending','approved','rejected'] as $st)
        <option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ __('expenses.status_'.$st) }}</option>
        @endforeach
    </select>
    <select name="category" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
        <option value="">{{ __('expenses.all_categories') }}</option>
        @foreach($categories as $cat)
        <option value="{{ $cat }}" @selected(($filters['category'] ?? '') === $cat)>{{ __('expenses.cat_'.$cat) }}</option>
        @endforeach
    </select>
    <select name="project_id" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
        <option value="">{{ __('time.all_projects') }}</option>
        @foreach($projects as $project)
        <option value="{{ $project->id }}" @selected(($filters['project_id'] ?? '') == $project->id)>{{ $project->name }}</option>
        @endforeach
    </select>
    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
    <button class="rounded-xl bg-gray-800 px-4 py-2 text-sm text-white">{{ __('time.apply') }}</button>
</form>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
            <tr>
                <th class="px-4 py-3 text-start">{{ __('expenses.number') }}</th>
                <th class="px-4 py-3 text-start">{{ __('expenses.date') }}</th>
                <th class="px-4 py-3 text-start">{{ __('expenses.vendor') }}</th>
                <th class="px-4 py-3 text-start">{{ __('expenses.category') }}</th>
                <th class="px-4 py-3 text-start">{{ __('common.project') }}</th>
                <th class="px-4 py-3 text-end">{{ __('expenses.total') }}</th>
                <th class="px-4 py-3 text-start">{{ __('time.status') }}</th>
                <th class="px-4 py-3 text-end">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($expenses as $expense)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-mono text-xs">{{ $expense->expense_number }}</td>
                <td class="px-4 py-3 text-gray-700">{{ $expense->expense_date?->format('Y-m-d') }}</td>
                <td class="px-4 py-3">
                    <p class="text-gray-800">{{ $expense->vendor_name }}</p>
                    @if($expense->receipt_path)
                    <a href="{{ route('expenses.receipt', $expense) }}" class="text-xs text-blue-600"><i class="fas fa-paperclip me-1"></i>{{ __('expenses.receipt') }}</a>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-600">{{ __('expenses.cat_'.$expense->category) }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $expense->project?->name ?? '-' }}</td>
                <td class="px-4 py-3 text-end font-medium">{{ number_format((float) $expense->total, 2) }}</td>
                <td class="px-4 py-3">
                    @php $c = ['pending' => 'amber', 'approved' => 'emerald', 'rejected' => 'red'][$expense->status] ?? 'gray'; @endphp
                    <span class="rounded-full bg-{{ $c }}-100 px-2.5 py-1 text-xs text-{{ $c }}-700">{{ __('expenses.status_'.$expense->status) }}</span>
                    @if($expense->isInvoiced())
                    <a href="{{ route('invoices.show', $expense->invoiced_invoice_id) }}" class="mt-1 inline-block rounded-full bg-blue-100 px-2.5 py-1 text-xs text-blue-700">{{ __('expenses.invoiced') }}</a>
                    @endif
                    @if($expense->status === 'rejected' && $expense->rejection_reason)
                    <p class="mt-1 text-[11px] text-red-500">{{ $expense->rejection_reason }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 text-end whitespace-nowrap">
                    @if($expense->isPending())
                        @can('approve-expenses')
                        <form action="{{ route('expenses.approve', $expense) }}" method="POST" class="inline">
                            @csrf
                            <button class="text-emerald-600 hover:text-emerald-800 text-xs me-2"><i class="fas fa-check"></i></button>
                        </form>
                        <button type="button" onclick="document.getElementById('reject-{{ $expense->id }}').classList.toggle('hidden')" class="text-red-500 hover:text-red-700 text-xs me-2"><i class="fas fa-xmark"></i></button>
                        @endcan
                        <a href="{{ route('expenses.edit', $expense) }}" class="text-blue-600 hover:text-blue-800 text-xs me-2"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 text-xs"><i class="fas fa-trash"></i></button>
                        </form>
                        @can('approve-expenses')
                        <form id="reject-{{ $expense->id }}" action="{{ route('expenses.reject', $expense) }}" method="POST" class="hidden mt-2 flex gap-2">
                            @csrf
                            <input name="rejection_reason" required maxlength="500" placeholder="{{ __('expenses.reject_reason') }}" class="rounded-lg border border-gray-300 px-2 py-1 text-xs">
                            <button class="rounded-lg bg-red-600 px-2 py-1 text-xs text-white">{{ __('expenses.reject') }}</button>
                        </form>
                        @endcan
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">{{ __('expenses.no_data') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $expenses->links() }}</div>
@endsection
