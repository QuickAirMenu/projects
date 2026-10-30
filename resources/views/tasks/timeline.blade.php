@extends('layouts.app')
@section('title', __('task.timeline'))

@php
    $today = now();
    $minDate = $tasks->min('start_date') ?? $today;
    $maxDate = $tasks->max('due_date') ?? $today->copy()->addMonth();
    $startDate = \Carbon\Carbon::parse($minDate)->startOfMonth();
    $endDate = \Carbon\Carbon::parse($maxDate)->endOfMonth();
    $totalDays = $startDate->diffInDays($endDate) ?: 30;
    $months = [];
    $cursor = $startDate->copy();
    while ($cursor->lte($endDate)) {
        $months[] = ['date' => $cursor->copy(), 'days' => $cursor->daysInMonth];
        $cursor->addMonth();
    }
    $grouped = $tasks->groupBy(fn($t) => $t->project->name ?? 'Unassigned');
@endphp

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('task.timeline') }}</h1>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto custom-scrollbar">
        <div class="min-w-max" style="min-width: {{ max(600, $totalDays * 2.5 + 250) }}px;">
            {{-- Month headers --}}
            <div class="flex border-b border-gray-200 bg-gray-50" style="padding-left: 250px;">
                @foreach($months as $month)
                    <div class="text-center text-xs font-semibold text-gray-500 py-2 border-r border-gray-200" style="width: {{ $month['days'] * 2.5 }}px;">
                        {{ $month['date']->translatedFormat('M Y') }}
                    </div>
                @endforeach
            </div>

            {{-- Timeline rows --}}
            <div class="relative">
                {{-- Vertical day lines --}}
                <div class="absolute inset-0 flex" style="left: 250px;">
                    @for($i = 0; $i <= $totalDays; $i++)
                        <div class="border-l border-gray-100 {{ $i % 7 === 0 ? 'border-gray-200' : '' }}" style="width: 2.5px;"></div>
                    @endfor
                </div>

                {{-- Today line --}}
                @php $todayOffset = $startDate->diffInDays($today); @endphp
                @if($todayOffset >= 0 && $todayOffset <= $totalDays)
                    <div class="absolute top-0 bottom-0 w-0.5 bg-red-500 z-10" style="left: calc(250px + {{ $todayOffset * 2.5 }}px);">
                        <div class="absolute -top-1 -translate-x-1/2 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded whitespace-nowrap">
                            {{ __('project.today') }}
                        </div>
                    </div>
                @endif

                {{-- Rows grouped by project --}}
                @forelse($grouped as $projectName => $projectTasks)
                    <div class="border-b border-gray-100">
                        {{-- Project header --}}
                        <div class="flex items-center h-8 bg-gray-50 border-b border-gray-100">
                            <div class="w-[250px] flex-shrink-0 px-4 text-xs font-semibold text-gray-600 truncate border-r border-gray-200">
                                {{ $projectName }}
                            </div>
                            <div class="text-xs text-gray-400 px-2">{{ $projectTasks->count() }} {{ __('task.tasks') }}</div>
                        </div>

                        {{-- Task rows --}}
                        @foreach($projectTasks as $task)
                            @php
                                $taskStart = $task->start_date ? \Carbon\Carbon::parse($task->start_date) : null;
                                $taskEnd = $task->due_date ? \Carbon\Carbon::parse($task->due_date) : null;
                                $taskStartOffset = $taskStart ? max(0, $startDate->diffInDays($taskStart)) : null;
                                $taskEndOffset = $taskEnd ? max(0, $startDate->diffInDays($taskEnd)) : null;
                                $taskWidth = ($taskStartOffset !== null && $taskEndOffset !== null) ? max(3, $taskEndOffset - $taskStartOffset) : null;
                                $taskColor = match($task->priority) { 'urgent' => '#ef4444', 'high' => '#f97316', 'medium' => '#3b82f6', default => '#6b7280' };
                            @endphp
                            <div class="flex items-center h-9 hover:bg-gray-50 transition border-b border-gray-50 last:border-0">
                                <div class="w-[250px] flex-shrink-0 px-4 text-sm truncate border-r border-gray-200">
                                    <a href="{{ route('tasks.show', $task) }}" class="text-gray-700 hover:text-blue-600">
                                        {{ $task->title }}
                                    </a>
                                </div>
                                <div class="relative flex-1 h-full" style="min-width: {{ $totalDays * 2.5 }}px;">
                                    @if($taskStartOffset !== null && $taskWidth !== null)
                                        <a href="{{ route('tasks.show', $task) }}"
                                           class="absolute top-1 rounded opacity-80 hover:opacity-100 transition-opacity cursor-pointer"
                                           style="left: {{ $taskStartOffset * 2.5 }}px; width: {{ $taskWidth * 2.5 }}px; height: 20px; background-color: {{ $taskColor }};"
                                           title="{{ $task->title }}">
                                            <div class="text-xs text-white px-1.5 leading-5 truncate">{{ $task->title }}</div>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400">
                        {{ __('task.no_tasks') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
