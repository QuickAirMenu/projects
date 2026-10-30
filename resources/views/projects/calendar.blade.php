@extends('layouts.app')
@section('title', __('project.calendar'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('project.calendar') }}</h1>
    <div class="flex items-center gap-3">
        <select id="projectFilter" class="border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('messages.all') }}</option>
            @foreach($projects as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
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
            month: '{{ __("project.calendar") }}',
            week: '{{ app()->getLocale() === "ar" ? "أسبوع" : "Week" }}'
        },
        height: 'auto',
        firstDay: {{ app()->getLocale() === 'ar' ? 6 : 0 }},
        events: {
            url: '{{ route("projects.calendar.data") }}',
            extraParams: function () {
                return {
                    project_id: document.getElementById('projectFilter').value
                };
            }
        },
        eventClick: function (info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
        },
        loading: function (isLoading) {
            if (isLoading) {
                calendarEl.classList.add('opacity-50');
            } else {
                calendarEl.classList.remove('opacity-50');
            }
        }
    });

    calendar.render();

    document.getElementById('projectFilter').addEventListener('change', function () {
        calendar.refetchEvents();
    });
});
</script>
@endpush
