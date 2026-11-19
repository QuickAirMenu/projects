@extends('layouts.app')
@section('title', $expense->exists ? __('expenses.edit') : __('expenses.new'))

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('expenses.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm"><i class="fas fa-arrow-{{ is_rtl() ? 'right' : 'left' }}"></i></a>
    <h1 class="text-2xl font-bold text-gray-800">{{ $expense->exists ? __('expenses.edit') : __('expenses.new') }}</h1>
</div>

<form action="{{ $expense->exists ? route('expenses.update', $expense) : route('expenses.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
    @csrf
    @if($expense->exists) @method('PUT') @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expenses.vendor') }}</label>
            <input name="vendor_name" required maxlength="150" value="{{ old('vendor_name', $expense->vendor_name) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
            @error('vendor_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expenses.category') }}</label>
            <select name="category" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                @foreach($categories as $cat)
                <option value="{{ $cat }}" @selected(old('category', $expense->category) === $cat)>{{ __('expenses.cat_'.$cat) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expenses.date') }}</label>
            <input type="date" name="expense_date" required value="{{ old('expense_date', $expense->expense_date?->format('Y-m-d')) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expenses.amount') }}</label>
            <input type="number" step="0.01" min="0.01" name="amount" required value="{{ old('amount', (float) $expense->amount) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expenses.tax_amount') }}</label>
            <input type="number" step="0.01" min="0" name="tax_amount" value="{{ old('tax_amount', (float) $expense->tax_amount) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('common.project') }}</label>
            <select name="project_id" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                <option value="">{{ __('time.select_project') }}</option>
                @foreach($projects as $project)
                <option value="{{ $project->id }}" @selected(old('project_id', $expense->project_id) == $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expenses.client') }}</label>
            <select name="client_id" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                <option value="">{{ __('expenses.none') }}</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}" @selected(old('client_id', $expense->client_id) == $client->id)>{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expenses.payment_method') }}</label>
            <input name="payment_method" maxlength="30" value="{{ old('payment_method', $expense->payment_method) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.paid_from') }}</label>
            <select name="account_id" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                <option value="">{{ __('expenses.none') }}</option>
                @foreach(\App\Models\Account::where('is_active', true)->orderBy('name')->get() as $account)
                <option value="{{ $account->id }}" @selected(old('account_id', $expense->account_id) == $account->id)>{{ $account->name }} ({{ $account->currency }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expenses.paid_at') }}</label>
            <input type="date" name="paid_at" value="{{ old('paid_at', $expense->paid_at?->format('Y-m-d')) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expenses.reference') }}</label>
            <input name="reference" maxlength="100" value="{{ old('reference', $expense->reference) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        </div>
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expenses.notes') }}</label>
            <textarea name="notes" rows="2" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">{{ old('notes', $expense->notes) }}</textarea>
        </div>
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expenses.receipt') }}</label>
            <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.webp,.pdf" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
            @if($expense->receipt_path)
            <a href="{{ route('expenses.receipt', $expense) }}" class="mt-1 inline-block text-xs text-blue-600"><i class="fas fa-paperclip me-1"></i>{{ $expense->receipt_name }}</a>
            @endif
        </div>
        <div class="md:col-span-3">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="billable" value="1" @checked(old('billable', $expense->billable)) class="rounded border-gray-300">
                {{ __('expenses.billable') }}
            </label>
        </div>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('expenses.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm">{{ __('messages.cancel') }}</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium">{{ __('messages.save') }}</button>
    </div>
</form>
@endsection
