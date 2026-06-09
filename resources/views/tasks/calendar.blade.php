@extends('layouts.app')
@section('title', __('task.calendar'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('task.calendar') }}</h1>
    <div class="flex items-center gap-2">
        <a href="{{ request()->fullUrlWithQuery(['month' => ($month - 1 <= 0 ? 12 : $month - 1), 'year' => ($month - 1 <= 0 ? $year - 1 : $year)]) }}" class="p-2 rounded-lg hover:bg-gray-100">
            <i class="fas fa-chevron-right"></i>
        </a>
        <span class="font-semibold text-gray-700 min-w-32 text-center">
            {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}
        </span>
        <a href="{{ request()->fullUrlWithQuery(['month' => ($month + 1 > 12 ? 1 : $month + 1), 'year' => ($month + 1 > 12 ? $year + 1 : $year)]) }}" class="p-2 rounded-lg hover:bg-gray-100">
            <i class="fas fa-chevron-left"></i>
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="grid grid-cols-7 text-center text-xs font-semibold text-gray-400 uppercase bg-gray-50 border-b">
        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
            <div class="py-2 border-r last:border-r-0">{{ $day }}</div>
        @endforeach
    </div>
    <div class="grid grid-cols-7">
        @php
            $start = \Carbon\Carbon::create($year, $month, 1)->startOfWeek();
            $end = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfWeek();
            $tasksByDate = $tasks->groupBy(fn($t) => $t->due_date->format('Y-m-d'));
        @endphp
        @while($start->lte($end))
            @php $dateKey = $start->format('Y-m-d'); $dayTasks = $tasksByDate->get($dateKey, collect()); @endphp
            <div class="min-h-24 p-1 border-r border-b text-xs {{ $start->month === $month ? 'bg-white' : 'bg-gray-50 text-gray-400' }}">
                <div class="text-right p-1 font-medium {{ $start->isToday() ? 'bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center' : '' }}">
                    {{ $start->day }}
                </div>
                @foreach($dayTasks->take(3) as $task)
                    <a href="{{ route('tasks.show', $task) }}" class="block truncate px-1 py-0.5 rounded mb-0.5 text-{{ $task->priority === 'urgent' ? 'red' : ($task->priority === 'high' ? 'orange' : 'blue') }}-600 bg-{{ $task->priority === 'urgent' ? 'red' : ($task->priority === 'high' ? 'orange' : 'blue') }}-50">
                        {{ $task->name }}
                    </a>
                @endforeach
                @if($dayTasks->count() > 3)
                    <span class="text-gray-400 px-1">+{{ $dayTasks->count() - 3 }}</span>
                @endif
            </div>
            @php $start->addDay() @endphp
        @endwhile
    </div>
</div>
@endsection
