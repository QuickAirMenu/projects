@extends('layouts.app')
@section('title', __('finance.add_invoice'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('finance.add_invoice') }}</h1>
    <a href="{{ route('invoices.index') }}" class="flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-xl transition text-sm">
        <i class="fas fa-arrow-right"></i> {{ __('messages.back') }}
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
@php
    $isEdit = isset($invoice);
    $invoiceModel = $invoice ?? null;
    $formAction = $isEdit ? route('invoices.update', $invoice) : route('invoices.store');
    $rows = old('items');
    if (!is_array($rows)) {
        $rows = $isEdit
            ? $invoice->items->map(fn($item) => [
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ])->toArray()
            : (!empty($prefillData['items']) ? $prefillData['items'] : [
                ['description' => '', 'quantity' => 1, 'unit_price' => 0],
            ])
        ;
    }
@endphp
<form action="{{ $formAction }}" method="POST">
@csrf
@if($isEdit) @method('PUT') @endif
@if(!empty($prefillData['quote']))
    <input type="hidden" name="quote_id" value="{{ $prefillData['quote']->id }}">
@endif
@if(!empty($prefillData))
    <div class="mb-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
        {{ __('finance.imported_quote_data', ['number' => $prefillData['quote']->quote_number]) }}
    </div>
@endif
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.project') }} *</label>
        <select name="project_id" required id="projectSelect" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">-- {{ __('messages.select') }} --</option>
            @foreach($projects as $p)
                <option value="{{ $p->id }}" data-client="{{ $p->client_id }}" {{ old('project_id', $invoiceModel?->project_id ?? $prefillData['project_id'] ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
        @error('project_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.client') }} *</label>
        <select name="client_id" required id="clientSelect" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- {{ __('messages.select') }} --</option>
            @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ old('client_id', $invoiceModel?->client_id ?? $prefillData['client_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        @error('client_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.invoice_type') }} *</label>
        <select name="invoice_type" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            @foreach(['initial','partial','final','additional'] as $t)
                <option value="{{ $t }}" {{ old('invoice_type') === $t ? 'selected' : '' }}>{{ __('finance.type_'.$t) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.invoice_date') }} *</label>
        <input type="date" name="invoice_date" value="{{ old('invoice_date', $invoiceModel?->invoice_date?->toDateString() ?? now()->toDateString()) }}" required
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
        @error('invoice_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.due_date') }}</label>
        <input type="date" name="due_date" value="{{ old('due_date', $invoiceModel?->due_date?->toDateString() ?? now()->addDays(30)->toDateString()) }}"
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
        @error('due_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.tax') }} %</label>
        <input type="number" name="tax_percentage" value="{{ old('tax_percentage', $isEdit ? $invoiceModel?->tax_percentage : 15) }}" step="0.01" min="0" max="100" id="taxInput"
               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.discount') }}</label>
        <input type="number" name="discount_amount" value="{{ old('discount_amount', $isEdit ? $invoiceModel?->discount_amount : 0) }}" step="0.01" min="0"
               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
</div>

<h3 class="font-semibold text-gray-700 mb-3">{{ __('finance.quote_items') }}</h3>
<div id="items-list" class="space-y-2 mb-3">
    @foreach($rows as $index => $row)
    <div class="grid grid-cols-12 gap-2 item-row">
        <div class="col-span-6"><input type="text" name="items[{{ $index }}][description]" value="{{ $row['description'] ?? '' }}" placeholder="{{ __('messages.description') }}" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">@error("items.{$index}.description")<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>
        <div class="col-span-2"><input type="number" name="items[{{ $index }}][quantity]" value="{{ $row['quantity'] ?? 1 }}" min="1" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm qty-input focus:outline-none focus:ring-2 focus:ring-blue-500">@error("items.{$index}.quantity")<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>
        <div class="col-span-3"><input type="number" name="items[{{ $index }}][unit_price]" value="{{ $row['unit_price'] ?? 0 }}" step="0.01" min="0" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm price-input focus:outline-none focus:ring-2 focus:ring-blue-500">@error("items.{$index}.unit_price")<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>
            <div class="col-span-1 flex items-center justify-center"><button type="button" class="text-red-400 hover:text-red-600 remove-row"><i class="fas fa-times"></i></button></div>
        </div>
        @endforeach
</div>

<div class="mt-6">
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.notes') }}</label>
    <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $invoiceModel?->notes ?? ($prefillData['description'] ?? '')) }}</textarea>
</div>
<button type="button" id="add-item" class="text-sm text-blue-600 hover:underline"><i class="fas fa-plus me-1"></i>{{ __('messages.add') }}</button>

<div class="mt-6 flex justify-end">
    <div class="w-64 space-y-2 text-sm">
        <div class="flex justify-between"><span class="text-gray-500">{{ __('finance.subtotal') }}</span><span id="subtotal">0.00</span></div>
        <div class="flex justify-between"><span class="text-gray-500">{{ __('finance.tax') }}</span><span id="taxAmt">0.00</span></div>
        <div class="flex justify-between border-t pt-2"><span class="font-semibold">{{ __('finance.total') }}</span><span id="total" class="font-bold text-blue-600">0.00</span></div>
    </div>
</div>

<div class="flex gap-3 mt-6">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
        <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
    </button>
    <a href="{{ route('invoices.index') }}" class="bg-gray-100 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition">{{ __('messages.cancel') }}</a>
</div>
</form>
</div>

@push('scripts')
<script>
let idx = {{ count($rows) }};
const prefillItems = @json(!empty($prefillData['items']) ? $prefillData['items'] : []);

if (prefillItems.length && document.querySelectorAll('.item-row').length === 1) {
    document.getElementById('items-list').innerHTML = '';
    prefillItems.forEach((item, index) => {
        document.getElementById('items-list').insertAdjacentHTML('beforeend', `
        <div class="grid grid-cols-12 gap-2 item-row">
            <div class="col-span-6"><input type="text" name="items[${index}][description]" value="${item.description ?? ''}" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div class="col-span-2"><input type="number" name="items[${index}][quantity]" value="${item.quantity ?? 1}" min="1" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm qty-input focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div class="col-span-3"><input type="number" name="items[${index}][unit_price]" value="${item.unit_price ?? 0}" step="0.01" min="0" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm price-input focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div class="col-span-1 flex items-center justify-center"><button type="button" class="text-red-400 hover:text-red-600 remove-row"><i class="fas fa-times"></i></button></div>
        </div>`);
    });
}

document.getElementById('add-item').addEventListener('click', () => {
    document.getElementById('items-list').insertAdjacentHTML('beforeend', `
        <div class="grid grid-cols-12 gap-2 item-row">
            <div class="col-span-6"><input type="text" name="items[${idx}][description]" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div class="col-span-2"><input type="number" name="items[${idx}][quantity]" value="1" min="1" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm qty-input focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div class="col-span-3"><input type="number" name="items[${idx}][unit_price]" value="0" step="0.01" min="0" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm price-input focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div class="col-span-1 flex items-center justify-center"><button type="button" class="text-red-400 hover:text-red-600 remove-row"><i class="fas fa-times"></i></button></div>
        </div>`);
    idx++;
    calc();
});
document.addEventListener('click', e => { if(e.target.closest('.remove-row')) { e.target.closest('.item-row').remove(); calc(); } });
document.addEventListener('input', e => { if(['qty-input','price-input'].some(c => e.target.classList.contains(c)) || e.target.id === 'taxInput') calc(); });
function calc() {
    let sub = 0;
    document.querySelectorAll('.item-row').forEach(r => { sub += (parseFloat(r.querySelector('.qty-input')?.value)||0) * (parseFloat(r.querySelector('.price-input')?.value)||0); });
    const tax = sub * (parseFloat(document.getElementById('taxInput').value)||0)/100;
    document.getElementById('subtotal').textContent = sub.toFixed(2);
    document.getElementById('taxAmt').textContent = tax.toFixed(2);
    document.getElementById('total').textContent = (sub+tax).toFixed(2);
}
</script>
@endpush
@endsection
