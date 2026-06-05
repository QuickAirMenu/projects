@extends('layouts.app')
@section('title', 'تقرير الأداء')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">تقرير الأداء</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $from }} — {{ $to }}</p>
    </div>
    <a href="{{ route('reports.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

{{-- Date Filter --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">من</label>
            <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">إلى</label>
            <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm transition"><i class="fas fa-filter me-1"></i>{{ __('messages.filter') }}</button>
        </div>
    </form>
</div>

{{-- Team Performance Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($performance as $member)
    @php
        $completionRate = $member->total_tasks > 0 ? round($member->completed_tasks / $member->total_tasks * 100) : 0;
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-lg font-bold text-blue-600 flex-shrink-0">
                {{ mb_substr($member->name,0,1) }}
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">{{ $member->name }}</h3>
                <p class="text-xs text-gray-400">{{ $member->job_title ?? $member->roles->first()?->name }}</p>
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">المهام الإجمالية</span>
                <span class="font-semibold text-gray-800">{{ $member->total_tasks }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">المكتملة</span>
                <span class="font-semibold text-green-600">{{ $member->completed_tasks }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">المتأخرة</span>
                <span class="font-semibold {{ $member->overdue_tasks > 0 ? 'text-red-500' : 'text-gray-400' }}">{{ $member->overdue_tasks }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">ساعات مسجّلة</span>
                <span class="font-semibold text-purple-600">{{ number_format($member->logged_hours, 1) }}h</span>
            </div>
        </div>

        {{-- Completion Rate Bar --}}
        <div class="mt-4">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>نسبة الإنجاز</span>
                <span>{{ $completionRate }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="h-2 rounded-full {{ $completionRate >= 80 ? 'bg-green-500' : ($completionRate >= 50 ? 'bg-yellow-400' : 'bg-red-400') }}"
                     style="width:{{ $completionRate }}%"></div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 py-16 text-center text-gray-400">
        <i class="fas fa-chart-bar text-5xl mb-3 block text-gray-200"></i>
        لا توجد بيانات أداء
    </div>
    @endforelse
</div>
@endsection
