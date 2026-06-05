@extends('layouts.app')
@section('title', __('task.calendar'))
@section('content')
<div class="mb-5 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('task.calendar') }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('tasks.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
            <i class="fas fa-list me-2"></i>{{ __('messages.view') }}
        </a>
        <a href="{{ route('tasks.kanban') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
            <i class="fas fa-columns me-2"></i>{{ __('task.kanban') }}
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <div id="calendar"></div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const cal = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        locale: '{{ app()->getLocale() }}',
        direction: '{{ app()->getLocale() === "ar" ? "rtl" : "ltr" }}',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
        events: @json($tasks),
        eventClick: info => { window.location.href = info.event.url; info.jsEvent.preventDefault(); },
        height: 'auto',
        eventDisplay: 'block',
    });
    cal.render();
});
</script>
@endpush
@endsection
