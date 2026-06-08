@extends('layouts.app')
@section('title', __('finance.edit_payment'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('finance.edit_payment') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $payment->payment_number }} · {{ $payment->invoice?->invoice_number }}</p>
    </div>
    <a href="{{ route('payments.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition">{{ __('messages.back') }}</a>
</div>


<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 max-w-3xl">
    <form action="{{ route('payments.update', $payment) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        @method('PUT')

        <input type="hidden" name="invoice_id" value="{{ old('invoice_id', $payment->invoice_id) }}">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance.invoice_number') }}</label>
            <input type="text" value="{{ $payment->invoice?->invoice_number }}" readonly class="w-full border border-gray-200 rounded-xl px-4 py-2.5 bg-gray-50 text-gray-600">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance.payment_date') }}</label>
            <input type="date" name="payment_date" value="{{ old('payment_date', optional($payment->payment_date)->format('Y-m-d')) }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('payment_date') border-red-400 @enderror">
            @error('payment_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance.paid_amount') }}</label>
            <input type="number" name="amount" step="0.01" value="{{ old('amount', $payment->amount) }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('amount') border-red-400 @enderror">
            @error('amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance.payment_method') }}</label>
            <select name="payment_method" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('payment_method') border-red-400 @enderror">
                @foreach(['bank_transfer','cash','cheque','card','online','other'] as $method)
                    <option value="{{ $method }}" @selected(old('payment_method', $payment->payment_method) === $method)>{{ __('finance.method_'.$method) }}</option>
                @endforeach
            </select>
            @error('payment_method')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance.transaction_ref') }}</label>
            <input type="text" name="transaction_reference" value="{{ old('transaction_reference', $payment->transaction_reference) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('transaction_reference') border-red-400 @enderror">
            @error('transaction_reference')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.notes') ?? 'ملاحظات' }}</label>
            <textarea name="notes" rows="4" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('notes') border-red-400 @enderror">{{ old('notes', $payment->notes) }}</textarea>
            @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('finance.payment_proof') }}</label>
            <input type="file" name="proof_of_payment" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('proof_of_payment') border-red-400 @enderror">
            <p class="text-xs text-gray-500 mt-2">{{ __('finance.payment_proof') }}. سيتم ضغط الصور تلقائياً قبل الرفع.</p>
            @if($payment->proof_file_path)
                <a href="{{ Storage::url($payment->proof_file_path) }}" target="_blank" class="inline-block mt-2 text-sm text-blue-600 hover:underline">{{ $payment->proof_file_name }}</a>
            @endif
            @error('proof_of_payment')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2 flex items-center gap-3 pt-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm transition">{{ __('finance.update_payment') }}</button>
            <a href="{{ route('payments.index') }}" class="bg-gray-100 text-gray-700 px-5 py-2.5 rounded-xl text-sm transition">{{ __('messages.back') }}</a>
        </div>
    </form>
</div>
@endsection
