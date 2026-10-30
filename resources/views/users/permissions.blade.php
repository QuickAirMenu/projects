@extends('layouts.app')
@section('title', __('users.manage_permissions'))
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('users.manage_permissions') }}</h1>
    <a href="{{ route('users.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Create Permission Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 h-fit">
        <h3 class="font-semibold text-gray-800 mb-4">{{ __('users.new_permission') }}</h3>
        <form action="{{ route('permissions.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.role_name') }} *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.guard_name') }}</label>
                <input type="text" name="guard_name" value="web" readonly
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition">
                <i class="fas fa-plus me-2"></i>{{ __('messages.add') }}
            </button>
        </form>
    </div>

    {{-- Permissions List --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('users.role_name') }}</th>
                        <th class="text-start px-4 py-3 font-semibold text-gray-600">{{ __('users.guard_name') }}</th>
                        <th class="text-end px-5 py-3 font-semibold text-gray-600">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($permissions as $perm)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3 text-gray-800">{{ $perm->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $perm->guard_name }}</td>
                        <td class="px-5 py-3 text-end">
                            <button type="button"
                                    x-data
                                    x-on:click="if (confirm('{{ __('users.delete_permission_confirm') }}')) { $el.closest('tr').querySelector('.delete-perm-form').submit() }"
                                    class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition">
                                <i class="fas fa-trash"></i>
                            </button>
                            <form action="{{ route('permissions.destroy', $perm) }}" method="POST" class="delete-perm-form hidden">
                                @csrf @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-16 text-center text-gray-400">
                            <i class="fas fa-shield-alt text-5xl mb-3 block text-gray-200"></i>
                            {{ __('users.no_permissions') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($permissions instanceof \Illuminate\Pagination\LengthAwarePaginator && $permissions->hasPages())
        <div class="px-5 py-4 border-t">{{ $permissions->links() }}</div>
        @endif
    </div>
</div>
@endsection
