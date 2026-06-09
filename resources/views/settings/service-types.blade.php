@extends('layouts.app')
@section('title', __('settings.service_types'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('settings.service_types') }}</h1>
    <a href="{{ route('settings.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Add Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">{{ __('settings.add_new_service_type') }}</h3>
        <form action="{{ route('settings.service-types.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('settings.name_ar') }} *</label>
                <input type="text" name="name_ar" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('settings.name_en') }} *</label>
                <input type="text" name="name_en" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('settings.icon') }}</label>
                <input type="text" name="icon" placeholder="fa-laptop" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('settings.color') }}</label>
                <input type="color" name="color" value="#3B82F6" class="w-full h-10 border border-gray-300 rounded-xl cursor-pointer">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl text-sm transition">
                <i class="fas fa-plus me-1"></i>{{ __('messages.add') }}
            </button>
        </form>
    </div>
    {{-- List --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 border-b">
                <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('settings.service_name') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">{{ __('settings.color') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">{{ __('settings.status_label') }}</th>
                <th class="text-end px-5 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($serviceTypes as $type)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            @if($type->icon)<i class="fas {{ $type->icon }}" style="color:{{ $type->color }}"></i>@endif
                            <span class="font-medium text-gray-800">{{ $type->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3"><div class="w-6 h-6 rounded-lg" style="background:{{ $type->color }}"></div></td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $type->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $type->is_active ? __('settings.active') : __('settings.inactive') }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-end">
                        <form action="{{ route('settings.service-types.destroy', $type) }}" method="POST" onsubmit="return confirmDelete(this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 text-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <tr class="bg-gray-50/50 border-b">
                    <td colspan="4" class="px-5 py-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-semibold text-gray-700">{{ __('settings.default_items') }}</h4>
                                <span class="text-xs text-gray-500">{{ $type->defaultItems->count() }} {{ __('settings.item_count') }}</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @forelse($type->defaultItems as $item)
                                    <div class="flex items-center justify-between bg-white border rounded-xl px-3 py-2 text-sm">
                                        <div>
                                            <p class="font-medium text-gray-800">{{ $item->description }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->quantity }} × {{ number_format($item->unit_price, 2) }}</p>
                                        </div>
                                        <form action="{{ route('settings.service-types.items.destroy', [$type, $item]) }}" method="POST" onsubmit="return confirmDelete(this)">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600 text-sm"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-400">{{ __('settings.no_default_items') }}</p>
                                @endforelse
                            </div>

                            <form action="{{ route('settings.service-types.items.store', $type) }}" method="POST" class="grid grid-cols-12 gap-2 items-end">
                                @csrf
                                <div class="col-span-5">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('messages.description') }}</label>
                                    <input type="text" name="description" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('finance.quantity') }}</label>
                                    <input type="number" name="quantity" value="1" min="1" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('settings.unit_price') }}</label>
                                    <input type="number" name="unit_price" step="0.01" min="0" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                </div>
                                <div class="col-span-2">
                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-3 py-2 text-sm">{{ __('settings.add_item') }}</button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-10 text-center text-gray-400">{{ __('settings.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
