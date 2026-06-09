@extends('layouts.app')
@section('title', __('messages.users'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.users') }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('roles.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
            <i class="fas fa-shield-alt me-2"></i>{{ __('messages.roles') }}
        </a>
        <a href="{{ route('users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm transition font-medium">
            <i class="fas fa-plus me-2"></i>{{ __('users.add_user') }}
        </a>
    </div>
</div>


{{-- Filters --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search') }}..."
               class="flex-1 min-w-48 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="role" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('messages.roles') }}</option>
            @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
            @endforeach
        </select>
        <select name="status" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('messages.status') }}</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>{{ __('users.active') }}</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>{{ __('users.inactive') }}</option>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm transition">
            <i class="fas fa-search me-1"></i>{{ __('messages.filter') }}
        </button>
        <a href="{{ route('users.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-xl text-sm transition">{{ __('messages.reset') }}</a>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('users.user') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">{{ __('users.email') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('users.role') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('users.job_title') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">{{ __('messages.status') }}</th>
                <th class="text-end px-5 py-3 font-semibold text-gray-600">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-600 flex-shrink-0">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $user->phone }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $user->email }}</td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    @foreach($user->roles as $role)
                    <span class="px-2 py-0.5 rounded-full text-xs bg-purple-100 text-purple-700 me-1">{{ $role->name }}</span>
                    @endforeach
                </td>
                <td class="px-4 py-3 hidden lg:table-cell text-gray-500">{{ $user->job_title ?? '-' }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                        {{ $user->is_active ? __('users.active') : __('users.inactive') }}
                    </span>
                </td>
                <td class="px-5 py-3 text-end">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('users.show', $user) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('users.edit', $user) }}" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg transition"><i class="fas fa-edit"></i></a>
                        @if(is_null($user->email_verified_at))
                        <form action="{{ route('users.invite', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-1.5 text-indigo-400 hover:bg-indigo-50 rounded-lg transition" title="{{ __('users.send_invitation') }}">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('users.toggle-status', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-1.5 {{ $user->is_active ? 'text-orange-400 hover:bg-orange-50' : 'text-green-500 hover:bg-green-50' }} rounded-lg transition"
                                    title="{{ $user->is_active ? __('users.deactivate') : __('users.activate') }}">
                                <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                            </button>
                        </form>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirmDelete(this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-16 text-center text-gray-400">
                <i class="fas fa-users text-5xl mb-3 block text-gray-200"></i>
                {{ __('users.no_users') }}
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($users->hasPages())
    <div class="px-5 py-4 border-t">{{ $users->links() }}</div>
    @endif
</div>
@endsection
