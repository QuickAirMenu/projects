@extends('layouts.app')
@section('title', __('messages.audit_log'))

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.audit_log') }}</h1>
    <p class="mt-1 text-sm text-gray-500">{{ __('messages.audit_log_hint') }}</p>
</div>

<form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 flex flex-wrap gap-3">
    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('messages.search') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm min-w-56">
    <select name="user_id" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
        <option value="">{{ __('messages.all_users') }}</option>
        @foreach($users as $user)
        <option value="{{ $user->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
        @endforeach
    </select>
    <select name="event" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
        <option value="">{{ __('messages.all_events') }}</option>
        @foreach($events as $event)
        <option value="{{ $event }}" @selected(($filters['event'] ?? '') === $event)>{{ $event }}</option>
        @endforeach
    </select>
    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
    <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm">{{ __('messages.filter') }}</button>
    <a href="{{ route('settings.audit-log') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-xl text-sm">{{ __('messages.reset') }}</a>
</form>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
            <tr>
                <th class="px-4 py-3 text-start">{{ __('messages.date') }}</th>
                <th class="px-4 py-3 text-start">{{ __('messages.user') }}</th>
                <th class="px-4 py-3 text-start">{{ __('messages.event') }}</th>
                <th class="px-4 py-3 text-start">{{ __('messages.description') }}</th>
                <th class="px-4 py-3 text-start">{{ __('messages.subject') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($activities as $activity)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $activity->created_at?->format('Y-m-d H:i:s') }}</td>
                <td class="px-4 py-3 text-gray-700">{{ $activity->causer?->name ?? '-' }}</td>
                <td class="px-4 py-3">
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs text-gray-600">{{ $activity->event ?? 'log' }}</span>
                </td>
                <td class="px-4 py-3 text-gray-700">{{ $activity->description }}</td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ $activity->subject_type ? class_basename($activity->subject_type).' #'.$activity->subject_id : '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">{{ __('messages.no_data') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $activities->links() }}</div>
@endsection
