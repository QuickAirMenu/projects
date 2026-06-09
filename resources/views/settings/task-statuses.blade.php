@extends('layouts.app')
@section('title', __('settings.task_statuses'))
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('settings.task_statuses') }}</h1>
    <a href="{{ route('settings.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">{{ __('settings.add_new_status') }}</h3>
        <form action="{{ route('settings.task-statuses.store') }}" method="POST" class="space-y-3">
            @csrf
            <div><label class="block text-xs font-medium text-gray-700 mb-1">{{ __('settings.name_ar') }} *</label>
                <input type="text" name="name_ar" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-xs font-medium text-gray-700 mb-1">{{ __('settings.name_en') }} *</label>
                <input type="text" name="name_en" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-xs font-medium text-gray-700 mb-1">{{ __('settings.slug') }} *</label>
                <input type="text" name="slug" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-xs font-medium text-gray-700 mb-1">{{ __('settings.color') }}</label>
                <input type="color" name="color" value="#3B82F6" class="w-full h-10 border border-gray-300 rounded-xl cursor-pointer"></div>
            <div><label class="block text-xs font-medium text-gray-700 mb-1">{{ __('settings.sort_order') }}</label>
                <input type="number" name="sort_order" value="{{ $statuses->count() + 1 }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl text-sm transition">
                <i class="fas fa-plus me-1"></i>{{ __('messages.add') }}
            </button>
        </form>
    </div>
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 border-b">
                <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('messages.status') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">{{ __('settings.slug') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">{{ __('settings.color') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">{{ __('settings.sort_order') }}</th>
                <th class="text-end px-5 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($statuses as $status)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full flex-shrink-0" style="background:{{ $status->color }}"></div>
                            <input type="text" name="name_ar" value="{{ $status->name_ar }}" form="edit-status-{{ $status->id }}"
                                   class="border border-gray-300 rounded-lg px-2 py-1 text-sm w-24" placeholder="AR">
                            <input type="text" name="name_en" value="{{ $status->name_en }}" form="edit-status-{{ $status->id }}"
                                   class="border border-gray-300 rounded-lg px-2 py-1 text-sm w-24" placeholder="EN">
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $status->slug }}</td>
                    <td class="px-4 py-3">
                        <input type="color" name="color" value="{{ $status->color }}" form="edit-status-{{ $status->id }}"
                               class="w-8 h-8 border border-gray-200 rounded cursor-pointer">
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" name="sort_order" value="{{ $status->sort_order }}" form="edit-status-{{ $status->id }}"
                               class="border border-gray-300 rounded-lg px-2 py-1 text-sm w-16 text-center">
                    </td>
                    <td class="px-5 py-3 text-end">
                        <div class="flex items-center justify-end gap-1">
                            <form id="edit-status-{{ $status->id }}" action="{{ route('settings.task-statuses.update', $status) }}" method="POST" class="inline">
                                @csrf @method('PUT')
                                <button type="submit" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"><i class="fas fa-save"></i></button>
                            </form>
                            <form action="{{ route('settings.task-statuses.destroy', $status) }}" method="POST" onsubmit="return confirmDelete(this)" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-10 text-center text-gray-400">{{ __('settings.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
