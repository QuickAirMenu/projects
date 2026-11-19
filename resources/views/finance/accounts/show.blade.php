@extends('layouts.app')
@section('title', $account->name)

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-3">
        <a href="{{ route('accounts.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm"><i class="fas fa-arrow-{{ is_rtl() ? 'right' : 'left' }}"></i></a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $account->name }}</h1>
            <p class="text-sm text-gray-500">{{ __('accounts.type_'.$account->type) }} · {{ $account->currency }} @if($account->iban) · {{ $account->iban }} @endif</p>
            <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-{{ $account->color() }}-100 px-2.5 py-0.5 text-xs text-{{ $account->color() }}-700">
                <i class="fas {{ $account->icon() }}"></i>
                {{ $account->nature() === 'liability' ? '− ' : '+ ' }}{{ __('accounts.nature_'.$account->nature()) }}
            </span>
        </div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('accounts.export', $account) }}" class="bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm"><i class="fas fa-file-csv me-2"></i>CSV</a>
        <a href="{{ route('accounts.edit', $account) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm"><i class="fas fa-edit me-1"></i>{{ __('messages.edit') }}</a>
        @if($account->canDelete())
        <form action="{{ route('accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('{{ __('accounts.confirm_delete') }}')">
            @csrf @method('DELETE')
            <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded-xl text-sm"><i class="fas fa-trash me-1"></i>{{ __('messages.delete') }}</button>
        </form>
        @else
        <span class="inline-flex items-center gap-1 rounded-xl bg-gray-50 px-3 py-2 text-xs text-gray-400" title="{{ __('accounts.cannot_delete') }}">
            <i class="fas fa-lock"></i> {{ __('accounts.has_movements') }}
        </span>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs text-gray-500">{{ __('accounts.balance') }}</p>
        <p class="mt-2 text-2xl font-bold {{ $balance >= 0 ? 'text-gray-800' : 'text-red-600' }}">{{ number_format($balance, 2) }} {{ $account->currency }}</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs text-gray-500">{{ __('accounts.base_balance', ['currency' => $baseCurrency]) }}</p>
        <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($baseBalance, 2) }}</p>
    </div>
</div>

<form action="{{ route('accounts.transactions.store', $account) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 grid grid-cols-2 md:grid-cols-6 gap-2 items-end">
    @csrf
    <div>
        <label class="text-xs text-gray-500">{{ __('accounts.direction') }}</label>
        <select name="direction" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"><option value="in">{{ __('accounts.in') }}</option><option value="out">{{ __('accounts.out') }}</option></select>
    </div>
    <div>
        <label class="text-xs text-gray-500">{{ __('accounts.amount') }}</label>
        <input name="amount" type="number" step="0.01" min="0.01" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-xs text-gray-500">{{ __('messages.date') }}</label>
        <input name="transaction_date" type="date" value="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-xs text-gray-500">{{ __('accounts.category') }}</label>
        <select name="category" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            @foreach(['client_payment','team_payout','expense','adjustment','other'] as $cat)
            <option value="{{ $cat }}">{{ __('accounts.cat_'.$cat) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-xs text-gray-500">{{ __('accounts.exchange_rate') }}</label>
        <input name="exchange_rate" type="number" step="0.000001" min="0.000001" value="{{ $account->exchange_rate_to_base }}" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
    </div>
    <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm text-white">{{ __('accounts.add_movement') }}</button>
    <input name="description" placeholder="{{ __('accounts.description') }}" class="col-span-2 md:col-span-4 rounded-xl border border-gray-300 px-3 py-2 text-sm">
    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp" class="col-span-2 rounded-xl border border-gray-300 px-3 py-2 text-sm">
</form>

<form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 mb-4 flex flex-wrap gap-2">
    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
    <select name="status" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
        <option value="">{{ __('accounts.all') }}</option>
        <option value="unreconciled" @selected(($filters['status'] ?? '') === 'unreconciled')>{{ __('accounts.unreconciled') }}</option>
    </select>
    <button class="rounded-xl bg-gray-800 px-4 py-2 text-sm text-white">{{ __('messages.filter') }}</button>
</form>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
            <tr>
                <th class="px-3 py-3 text-start">{{ __('messages.date') }}</th>
                <th class="px-3 py-3 text-start">{{ __('accounts.description') }}</th>
                <th class="px-3 py-3 text-start">{{ __('accounts.category') }}</th>
                <th class="px-3 py-3 text-end">{{ __('accounts.in') }}</th>
                <th class="px-3 py-3 text-end">{{ __('accounts.out') }}</th>
                <th class="px-3 py-3 text-end">{{ __('accounts.running_balance') }}</th>
                <th class="px-3 py-3 text-end">{{ __('accounts.reconciled') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($rows as $row)
            @php $tx = $row['tx']; @endphp
            <tr class="{{ $tx->reconciled_at ? 'bg-emerald-50/40' : '' }}">
                <td class="px-3 py-2 whitespace-nowrap text-gray-600">{{ $tx->transaction_date?->format('Y-m-d') }}</td>
                <td class="px-3 py-2 text-gray-700">
                    {{ $tx->description ?? '-' }}
                    @if($tx->attachment_path)
                    <a href="{{ route('accounts.attachment', [$account, $tx]) }}" class="ms-1 text-blue-600" title="{{ $tx->attachment_name }}"><i class="fas fa-paperclip"></i></a>
                    @endif
                </td>
                <td class="px-3 py-2 text-xs text-gray-500">{{ __('accounts.cat_'.$tx->category) }}</td>
                <td class="px-3 py-2 text-end text-emerald-600">{{ $tx->direction === 'in' ? number_format($tx->amount, 2) : '-' }}</td>
                <td class="px-3 py-2 text-end text-red-500">{{ $tx->direction === 'out' ? number_format($tx->amount, 2) : '-' }}</td>
                <td class="px-3 py-2 text-end font-medium">{{ number_format($row['balance'], 2) }}</td>
                <td class="px-3 py-2 text-end">
                    <form action="{{ route('accounts.reconcile', [$account, $tx]) }}" method="POST">
                        @csrf
                        <button class="text-xs {{ $tx->reconciled_at ? 'text-emerald-600' : 'text-gray-400 hover:text-gray-600' }}"><i class="fas {{ $tx->reconciled_at ? 'fa-circle-check' : 'fa-circle' }}"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">{{ __('accounts.no_movements') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
