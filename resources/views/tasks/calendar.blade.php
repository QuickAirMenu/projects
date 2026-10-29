@extends('layouts.app')
@section('title', __('task.calendar'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('task.calendar') }}</h1>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
    <div id="calendar"></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const { Calendar, dayGridPlugin, timeGridPlugin, interactionPlugin } = window.FullCalendar;

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        locale: '{{ app()->getLocale() === 'ar' ? 'ar' : 'en' }}',
        direction: '{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}',
        buttonText: {
            today: '{{ __("project.today") }}',
            month: '{{ __("task.calendar") }}',
            week: '{{ app()->getLocale() === "ar" ? "أسبوع" : "Week" }}'
        },
        height: 'auto',
        firstDay: {{ app()->getLocale() === 'ar' ? 6 : 0 }},
        events: {
            url: '{{ route("tasks.calendar.data") }}',
        },
        eventClick: function (info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
        },
        eventDidMount: function (info) {
            info.el.setAttribute('title', info.event.title);
        },
        loading: function (isLoading) {
            calendarEl.classList.toggle('opacity-50', isLoading);
        }
    });

    calendar.render();
});
</script>
@endpush
