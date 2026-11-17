@extends('layouts.app')
@section('title', $recurring->exists ? __('messages.edit_recurring_invoice') : __('messages.new_recurring_invoice'))

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('recurring-invoices.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm"><i class="fas fa-arrow-{{ is_rtl() ? 'right' : 'left' }}"></i></a>
    <h1 class="text-2xl font-bold text-gray-800">{{ $recurring->exists ? __('messages.edit_recurring_invoice') : __('messages.new_recurring_invoice') }}</h1>
</div>

<form action="{{ $recurring->exists ? route('recurring-invoices.update', $recurring) : route('recurring-invoices.store') }}" method="POST" class="space-y-6">
    @csrf
    @if($recurring->exists) @method('PUT') @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.name') }}</label>
                <input name="name" required maxlength="120" value="{{ old('name', $recurring->name) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('common.client') }}</label>
                <select name="client_id" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                    <option value="">{{ __('messages.select') }}</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" @selected(old('client_id', $recurring->client_id) == $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
                @error('client_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('common.project') }}</label>
                <select name="project_id" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                    <option value="">{{ __('messages.select') }}</option>
                    @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected(old('project_id', $recurring->project_id) == $project->id)>{{ $project->name }} — {{ $project->client?->name }}</option>
                    @endforeach
                </select>
                @error('project_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.invoice_type') }}</label>
                <select name="invoice_type" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                    @foreach(['initial','partial','final','additional'] as $type)
                    <option value="{{ $type }}" @selected(old('invoice_type', $recurring->invoice_type) === $type)>{{ __('finance.type_'.$type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.frequency') }}</label>
                <select name="frequency" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                    @foreach(['monthly','quarterly','yearly'] as $freq)
                    <option value="{{ $freq }}" @selected(old('frequency', $recurring->frequency) === $freq)>{{ __('finance.freq_'.$freq) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.next_run') }}</label>
                <input type="date" name="next_run_date" required value="{{ old('next_run_date', $recurring->next_run_date?->format('Y-m-d')) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                @error('next_run_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.tax_percentage') }} %</label>
                <input type="number" step="0.01" min="0" max="100" name="tax_percentage" value="{{ old('tax_percentage', (float) $recurring->tax_percentage) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.discount_type') }}</label>
                <select name="discount_type" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
                    <option value="fixed" @selected(old('discount_type', $recurring->discount_type) === 'fixed')>{{ __('finance.discount_fixed') }}</option>
                    <option value="percentage" @selected(old('discount_type', $recurring->discount_type) === 'percentage')>{{ __('finance.discount_percentage') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.discount_amount') }}</label>
                <input type="number" step="0.01" min="0" name="discount_amount" value="{{ old('discount_amount', (float) $recurring->discount_amount) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
            </div>
            <div class="md:col-span-3 flex items-center gap-2">
                <input id="isActive" type="checkbox" name="is_active" value="1" @checked(old('is_active', $recurring->is_active)) class="rounded border-gray-300">
                <label for="isActive" class="text-sm text-gray-700">{{ __('finance.active') }}</label>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-800">{{ __('finance.items') }}</h2>
            <button type="button" onclick="addRow()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-xs"><i class="fas fa-plus me-1"></i>{{ __('finance.add_item') }}</button>
        </div>
        <table class="w-full text-sm">
            <thead class="text-xs text-gray-500">
                <tr>
                    <th class="py-2 text-start">{{ __('finance.description') }}</th>
                    <th class="py-2 text-start w-24">{{ __('finance.quantity') }}</th>
                    <th class="py-2 text-start w-32">{{ __('finance.unit_price') }}</th>
                    <th class="w-10"></th>
                </tr>
            </thead>
            <tbody id="itemsBody">
                @php $rows = old('items', $recurring->items ?: [['description'=>'','quantity'=>1,'unit_price'=>0]]); @endphp
                @foreach($rows as $i => $row)
                <tr>
                    <td class="py-1 pe-2"><input name="items[{{ $i }}][description]" required value="{{ $row['description'] ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></td>
                    <td class="py-1 pe-2"><input type="number" min="1" name="items[{{ $i }}][quantity]" required value="{{ $row['quantity'] ?? 1 }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></td>
                    <td class="py-1 pe-2"><input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_price]" required value="{{ $row['unit_price'] ?? 0 }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></td>
                    <td class="py-1"><button type="button" onclick="this.closest('tr').remove()" class="text-red-500"><i class="fas fa-times"></i></button></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @error('items')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.notes') }}</label>
            <textarea name="notes" rows="3" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">{{ old('notes', $recurring->notes) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('finance.terms') }}</label>
            <textarea name="terms" rows="3" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">{{ old('terms', $recurring->terms) }}</textarea>
        </div>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('recurring-invoices.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm">{{ __('messages.cancel') }}</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium">{{ __('messages.save') }}</button>
    </div>
</form>

@push('scripts')
<script>
let idx = {{ count($rows) }};
function addRow() {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td class="py-1 pe-2"><input name="items[${idx}][description]" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></td>
    <td class="py-1 pe-2"><input type="number" min="1" name="items[${idx}][quantity]" required value="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></td>
    <td class="py-1 pe-2"><input type="number" step="0.01" min="0" name="items[${idx}][unit_price]" required value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></td>
    <td class="py-1"><button type="button" onclick="this.closest('tr').remove()" class="text-red-500"><i class="fas fa-times"></i></button></td>`;
    document.getElementById('itemsBody').appendChild(tr);
    idx++;
}
</script>
@endpush
@endsection
