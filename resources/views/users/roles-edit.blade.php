@extends('layouts.app')
@section('title', __('users.edit_role'))
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('users.edit_role') }}</h1>
    <a href="{{ route('roles.index') }}" class="flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-xl transition text-sm">
        <i class="fas fa-arrow-right"></i> {{ __('messages.back') }}
    </a>
</div>

<div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <form action="{{ route('roles.update', $role) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.role_name') }} *</label>
            <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-6" x-data="{ selectAll: false }">
            <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-medium text-gray-700">{{ __('users.permissions') }}</label>
                <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-600">
                    <input type="checkbox" x-model="selectAll" @change="document.querySelectorAll('.perm-checkbox').forEach(c => c.checked = selectAll)"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>{{ __('users.select_all') }}</span>
                </label>
            </div>
            @php
                $grouped = $permissions->groupBy(function($p) {
                    return explode('-', $p->name, 2)[1] ?? $p->name;
                });
            @endphp
            @foreach($grouped as $module => $perms)
            <div class="mb-4">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ $module }}</h4>
                <div class="flex flex-wrap gap-3">
                    @foreach($perms as $perm)
                    <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                               {{ in_array($perm->id, old('permissions', $rolePermissionIds ?? [])) ? 'checked' : '' }}
                               class="perm-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        {{ $perm->name }}
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
            @error('permissions')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
                <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
            </button>
            <a href="{{ route('roles.index') }}" class="bg-gray-100 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
