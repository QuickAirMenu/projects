@extends('layouts.app')
@section('title', __('finance.add_quote'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('finance.add_quote') }}</h1>
    <a href="{{ route('quotes.index') }}" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl transition text-sm">
        <i class="fas fa-arrow-right"></i> {{ __('messages.back') }}
    </a>
</div>


<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
@php
    $isEdit = isset($quote);
    $quoteModel = $quote ?? null;
    $formAction = $isEdit ? route('quotes.update', $quote) : route('quotes.store');
    $serviceTypeDefaults = [];
    foreach ($serviceTypes as $serviceType) {
        $serviceTypeDefaults[$serviceType->id] = $serviceType->defaultItems->map(fn($item) => [
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'service_type_id' => $serviceType->id,
        ])->values()->all();
    }
    $serviceTypesForJs = $serviceTypes->map(fn($serviceType) => [
        'id' => $serviceType->id,
        'name' => $serviceType->name,
    ])->values()->all();
    $defaultServiceTypeId = null;
    if (! $isEdit) {
        foreach ($serviceTypes as $serviceType) {
            if (! empty($serviceTypeDefaults[$serviceType->id])) {
                $defaultServiceTypeId = $serviceType->id;
                break;
            }
        }
    }
    $rows = old('items');
    if (!is_array($rows)) {
        $rows = $isEdit
            ? $quote->items->map(fn($item) => [
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'service_type_id' => $item->service_type_id,
            ])->toArray()
            : [['description' => '', 'quantity' => 1, 'unit_price' => 0, 'service_type_id' => '']];
    }
    if (!$isEdit && count($rows) === 1 && blank($rows[0]['service_type_id'] ?? null) && $defaultServiceTypeId) {
        $rows[0]['service_type_id'] = $defaultServiceTypeId;
    }
@endphp
<form action="{{ $formAction }}" method="POST" id="quoteForm">
@csrf
@if($isEdit) @method('PUT') @endif
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.client') }} *</label>
        <select name="client_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">-- {{ __('messages.select') }} --</option>
            @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ old('client_id', $quoteModel?->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.description') }} *</label>
        <input type="text" name="subject" value="{{ old('subject', $quoteModel?->subject ?? '') }}" required
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.valid_until') }}</label>
        <input type="date" name="valid_until" value="{{ old('valid_until', $quoteModel?->valid_until?->toDateString() ?? now()->addDays(30)->toDateString()) }}"
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.tax') }} %</label>
        <input type="number" name="tax_percentage" value="{{ old('tax_percentage', $isEdit ? $quoteModel?->tax_percentage : 15) }}" step="0.01" min="0" max="100"
               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" id="taxInput">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.discount') }} %</label>
        <input type="number" name="discount_percentage" value="{{ old('discount_percentage', $isEdit ? $quoteModel?->discount_percentage : 0) }}" step="0.01" min="0" max="100"
               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
</div>

{{-- Items --}}
<h3 class="font-semibold text-gray-700 mb-3">{{ __('finance.quote_items') }}</h3>
<div id="items-container">
    <div class="grid grid-cols-12 gap-2 mb-2 text-xs text-gray-500 px-1">
        <div class="col-span-4">{{ __('messages.description') }}</div>
        <div class="col-span-2">{{ __('task.hours') }}</div>
        <div class="col-span-2">{{ __('finance.method_other') }}</div>
        <div class="col-span-3">{{ __('project.service_type') }}</div>
        <div class="col-span-1"></div>
    </div>
    <div id="items-list">
        @foreach($rows as $index => $row)
        @php $rowServiceTypeId = $row['service_type_id'] ?? ''; @endphp
        <div class="grid grid-cols-12 gap-2 mb-2 item-row">
            <div class="col-span-4"><input type="text" name="items[{{ $index }}][description]" value="{{ $row['description'] ?? '' }}" placeholder="{{ __('messages.description') }}" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div class="col-span-2"><input type="number" name="items[{{ $index }}][quantity]" value="{{ $row['quantity'] ?? 1 }}" min="1" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 qty-input"></div>
            <div class="col-span-2"><input type="number" name="items[{{ $index }}][unit_price]" value="{{ $row['unit_price'] ?? 0 }}" min="0" step="0.01" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 price-input"></div>
            <div class="col-span-3">
                <select name="items[{{ $index }}][service_type_id]" class="service-type-select w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- {{ __('messages.select') }} --</option>
                    @foreach($serviceTypes as $serviceType)
                        <option value="{{ $serviceType->id }}" @selected((string)$rowServiceTypeId === (string)$serviceType->id)>
                            {{ $serviceType->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-1 flex items-center justify-center"><button type="button" class="text-red-400 hover:text-red-600 remove-row"><i class="fas fa-times"></i></button></div>
        </div>
        @endforeach
    </div>
</div>
<button type="button" id="add-item" class="mt-2 text-sm text-blue-600 hover:underline"><i class="fas fa-plus me-1"></i>{{ __('messages.add') }}</button>

{{-- Totals --}}
<div class="mt-6 flex justify-end">
    <div class="w-64 space-y-2 text-sm">
        <div class="flex justify-between"><span class="text-gray-500">{{ __('finance.subtotal') }}</span><span id="subtotal" class="font-medium">0.00</span></div>
        <div class="flex justify-between"><span class="text-gray-500">{{ __('finance.tax') }}</span><span id="taxAmount" class="font-medium">0.00</span></div>
        <div class="flex justify-between border-t pt-2"><span class="font-semibold">{{ __('finance.total') }}</span><span id="total" class="font-bold text-blue-600">0.00</span></div>
    </div>
</div>

<div class="mt-6">
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.notes') }}</label>
        <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $quoteModel?->notes ?? '') }}</textarea>
</div>

<div class="flex gap-3 mt-6">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
        <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
    </button>
    <a href="{{ route('quotes.index') }}" class="bg-gray-100 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition">{{ __('messages.cancel') }}</a>
</div>
</form>
</div>

@push('scripts')
<script>
const serviceTypes = @json($serviceTypesForJs);
const serviceTypeDefaults = @json($serviceTypeDefaults);
let idx = {{ count($rows) }};

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    }[char]));
}

function renderServiceTypeOptions(selectedId = '') {
    return [
        '<option value="">-- {{ __('messages.select') }} --</option>',
        ...serviceTypes.map(serviceType => `<option value="${serviceType.id}" ${String(selectedId) === String(serviceType.id) ? 'selected' : ''}>${escapeHtml(serviceType.name)}</option>`)
    ].join('');
}

function serviceTypeSelect(index, selectedId = '') {
    return `<select name="items[${index}][service_type_id]" class="service-type-select w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">${renderServiceTypeOptions(selectedId)}</select>`;
}

function rowTemplate(index, item = {}) {
    return `<div class="grid grid-cols-12 gap-2 mb-2 item-row">
        <div class="col-span-4"><input type="text" name="items[${index}][description]" value="${item.description ?? ''}" placeholder="{{ __('messages.description') }}" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
        <div class="col-span-2"><input type="number" name="items[${index}][quantity]" value="${item.quantity ?? 1}" min="1" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 qty-input"></div>
        <div class="col-span-2"><input type="number" name="items[${index}][unit_price]" value="${item.unit_price ?? 0}" min="0" step="0.01" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 price-input"></div>
        <div class="col-span-3">${serviceTypeSelect(index, item.service_type_id ?? '')}</div>
        <div class="col-span-1 flex items-center justify-center"><button type="button" class="text-red-400 hover:text-red-600 remove-row"><i class="fas fa-times"></i></button></div>
    </div>`;
}

function loadDefaults(defaults) {
    const list = document.getElementById('items-list');
    list.innerHTML = '';
    defaults.forEach((item, index) => {
        list.insertAdjacentHTML('beforeend', rowTemplate(index, item));
    });
    idx = defaults.length;
    calcTotals();
}

function resetRows() {
    const list = document.getElementById('items-list');
    list.innerHTML = rowTemplate(0);
    idx = 1;
    calcTotals();
}

document.getElementById('add-item').addEventListener('click', () => {
    document.getElementById('items-list').insertAdjacentHTML('beforeend', rowTemplate(idx));
    idx++;
    calcTotals();
});

document.addEventListener('click', e => {
    if (e.target.closest('.remove-row')) {
        e.target.closest('.item-row').remove();
        calcTotals();
    }
});

document.addEventListener('input', e => {
    if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input') || e.target.id === 'taxInput') {
        calcTotals();
    }
});

document.addEventListener('change', e => {
    if (!e.target.classList.contains('service-type-select')) return;

    const rows = document.querySelectorAll('.item-row');
    if (rows.length !== 1) return;

    const defaults = serviceTypeDefaults[String(e.target.value)] ?? [];
    if (defaults.length) {
        loadDefaults(defaults);
    } else {
        resetRows();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const firstSelect = document.querySelector('.item-row .service-type-select');
    const rows = document.querySelectorAll('.item-row');
    if (firstSelect && rows.length === 1 && firstSelect.value) {
        const defaults = serviceTypeDefaults[String(firstSelect.value)] ?? [];
        if (defaults.length) {
            loadDefaults(defaults);
        }
    }
});

function calcTotals() {
    let sub = 0;
    document.querySelectorAll('.item-row').forEach(r => {
        const q = parseFloat(r.querySelector('.qty-input')?.value) || 0;
        const p = parseFloat(r.querySelector('.price-input')?.value) || 0;
        sub += q * p;
    });
    const tax = sub * (parseFloat(document.getElementById('taxInput')?.value) || 0) / 100;
    document.getElementById('subtotal').textContent = sub.toFixed(2);
    document.getElementById('taxAmount').textContent = tax.toFixed(2);
    document.getElementById('total').textContent = (sub + tax).toFixed(2);
}
</script>
@endpush
@endsection
