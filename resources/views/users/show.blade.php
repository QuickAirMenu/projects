@extends('layouts.app')
@section('title', $user->name)
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('users.edit', $user) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-edit me-2"></i>تعديل</a>
        <a href="{{ route('users.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition"><i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}</a>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
        <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center text-3xl font-bold text-blue-600 mx-auto mb-4">
            {{ mb_substr($user->name,0,1) }}
        </div>
        <h2 class="font-bold text-gray-800 text-lg">{{ $user->name }}</h2>
        <p class="text-gray-500 text-sm">{{ $user->job_title ?? '' }}</p>
        <div class="flex flex-wrap justify-center gap-1 mt-2">
            @foreach($user->roles as $role)
            <span class="px-2 py-0.5 rounded-full text-xs bg-purple-100 text-purple-700">{{ $role->name }}</span>
            @endforeach
        </div>
        <div class="mt-4 space-y-2 text-sm text-start">
            @if($user->email)<div class="flex items-center gap-2 text-gray-600"><i class="fas fa-envelope w-4 text-blue-400"></i>{{ $user->email }}</div>@endif
            @if($user->phone)<div class="flex items-center gap-2 text-gray-600"><i class="fas fa-phone w-4 text-blue-400"></i>{{ $user->phone }}</div>@endif
        </div>
        <div class="mt-4 pt-4 border-t">
            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                {{ $user->is_active ? 'نشط' : 'معطل' }}
            </span>
        </div>
    </div>
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 mb-3">المهام المسندة</h3>
            @php $tasks = $user->assignedTasks()->with('status','project')->whereHas('status', fn($q) => $q->whereNotIn('slug',['done']))->latest()->take(5)->get(); @endphp
            @forelse($tasks as $task)
            <div class="flex items-center justify-between py-2 border-b last:border-0 text-sm">
                <a href="{{ route('tasks.show', $task) }}" class="text-gray-700 hover:text-blue-600">{{ $task->title }}</a>
                <span class="px-2 py-0.5 rounded-full text-xs" style="background:{{ $task->status->color }}20;color:{{ $task->status->color }}">{{ $task->status->name }}</span>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4">لا توجد مهام مفتوحة</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
