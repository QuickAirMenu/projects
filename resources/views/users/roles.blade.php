@extends('layouts.app')
@section('title', __('messages.roles'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.roles') }} & {{ __('messages.permissions') }}</h1>
    <a href="{{ route('users.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($roles as $role)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-800">{{ $role->name }}</h3>
            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ $role->users_count }} مستخدم</span>
        </div>
        <div class="flex flex-wrap gap-1 mb-4">
            @foreach($role->permissions->take(5) as $perm)
            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $perm->name }}</span>
            @endforeach
            @if($role->permissions->count() > 5)
            <span class="text-xs text-gray-400">+{{ $role->permissions->count() - 5 }} أخرى</span>
            @endif
        </div>
        <a href="{{ route('roles.edit', $role) }}" class="text-sm text-blue-600 hover:underline">
            <i class="fas fa-edit me-1"></i>تعديل الصلاحيات
        </a>
    </div>
    @empty
    <div class="col-span-3 py-16 text-center text-gray-400">لا توجد أدوار</div>
    @endforelse
</div>
@endsection
