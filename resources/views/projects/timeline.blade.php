@extends('layouts.app')
@section('title', __('project.timeline'))

@php
    $today = now();
    $minDate = $projects->min('start_date') ?? $today;
    $maxDate = $projects->max('deadline') ?? $today->copy()->addMonth();
    $startDate = \Carbon\Carbon::parse($minDate)->startOfMonth();
    $endDate = \Carbon\Carbon::parse($maxDate)->endOfMonth();
    $totalDays = $startDate->diffInDays($endDate) ?: 30;
    $months = [];
    $cursor = $startDate->copy();
    while ($cursor->lte($endDate)) {
        $months[] = ['date' => $cursor->copy(), 'days' => $cursor->daysInMonth];
        $cursor->addMonth();
    }
@endphp

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('project.timeline') }}</h1>
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

            {{-- Day grid lines + rows --}}
            <div class="relative">
                {{-- Vertical day lines --}}
                <div class="absolute inset-0 flex" style="left: 250px;">
                    @for($i = 0; $i <= $totalDays; $i++)
                        <div class="border-l border-gray-100 {{ $i % 7 === 0 ? 'border-gray-200' : '' }}" style="width: 2.5px;"></div>
                    @endfor
                </div>

                {{-- Today line --}}
                @php
                    $todayOffset = $startDate->diffInDays($today);
                @endphp
                @if($todayOffset >= 0 && $todayOffset <= $totalDays)
                    <div class="absolute top-0 bottom-0 w-0.5 bg-red-500 z-10" style="left: calc(250px + {{ $todayOffset * 2.5 }}px);">
                        <div class="absolute -top-1 -translate-x-1/2 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded whitespace-nowrap">
                            {{ __('project.today') }}
                        </div>
                    </div>
                @endif

                {{-- Project rows --}}
                @forelse($projects as $project)
                    @php
                        $projStart = $project->start_date ? \Carbon\Carbon::parse($project->start_date) : null;
                        $projEnd = $project->deadline ? \Carbon\Carbon::parse($project->deadline) : null;
                        $projStartOffset = $projStart ? max(0, $startDate->diffInDays($projStart)) : null;
                        $projWidth = ($projStart && $projEnd) ? max(5, $projStart->diffInDays($projEnd)) : null;
                        $statusColor = $project->status->color ?? '#6b7280';
                    @endphp
                    <div class="flex items-center border-b border-gray-100 hover:bg-gray-50 transition min-h-[60px]">
                        {{-- Project name --}}
                        <div class="w-[250px] flex-shrink-0 px-4 py-2 text-sm truncate border-r border-gray-200">
                            <a href="{{ route('projects.show', $project) }}" class="font-medium text-gray-800 hover:text-blue-600">
                                {{ $project->name }}
                            </a>
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ $project->status->name ?? '' }}
                            </div>
                        </div>

                        {{-- Timeline area --}}
                        <div class="relative h-[60px] flex-1" style="min-width: {{ $totalDays * 2.5 }}px;">
                            @if($projStartOffset !== null && $projWidth !== null)
                                {{-- Project bar --}}
                                <div class="absolute top-1 rounded-full opacity-80" style="left: {{ $projStartOffset * 2.5 }}px; width: {{ $projWidth * 2.5 }}px; height: 20px; background-color: {{ $statusColor }};">
                                    <div class="text-xs text-white px-2 leading-5 truncate font-medium">
                                        {{ $projStart?->format('d/m') }} — {{ $projEnd?->format('d/m') }}
                                    </div>
                                </div>

                                {{-- Task bars --}}
                                @foreach($project->tasks as $task)
                                    @php
                                        $taskStart = $task->start_date ? \Carbon\Carbon::parse($task->start_date) : null;
                                        $taskEnd = $task->due_date ? \Carbon\Carbon::parse($task->due_date) : null;
                                        $taskStartOffset = $taskStart ? max(0, $startDate->diffInDays($taskStart)) : null;
                                        $taskWidth = ($taskStart && $taskEnd) ? max(3, $taskStart->diffInDays($taskEnd)) : null;
                                        $taskColor = match($task->priority) { 'urgent' => '#ef4444', 'high' => '#f97316', 'medium' => '#3b82f6', default => '#6b7280' };
                                    @endphp
                                    @if($taskStartOffset !== null && $taskWidth !== null)
                                        <a href="{{ route('tasks.show', $task) }}"
                                           class="absolute rounded opacity-70 hover:opacity-100 transition-opacity cursor-pointer"
                                           style="left: {{ $taskStartOffset * 2.5 }}px; width: {{ $taskWidth * 2.5 }}px; height: 14px; top: 26px; background-color: {{ $taskColor }};"
                                           title="{{ $task->title }}">
                                            <div class="text-[10px] text-white px-1 leading-[14px] truncate">{{ $task->title }}</div>
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400">
                        {{ __('project.no_projects') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
