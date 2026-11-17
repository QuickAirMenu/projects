@extends('layouts.app')
@section('title', __('settings.backups'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('settings.backups') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $path }}</p>
    </div>
    <form action="{{ route('settings.backups.run') }}" method="POST" onsubmit="return confirm('{{ __('messages.backup_confirm') }}')">
        @csrf
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium" @disabled(! $scriptExists || ! $scriptExecutable)>
            <i class="fas fa-database me-2"></i>{{ __('messages.run_backup_now') }}
        </button>
    </form>
</div>

@if(session('backup_output'))
<div class="mb-4 rounded-2xl border border-gray-200 bg-gray-900 p-4 text-xs text-gray-100 overflow-auto max-h-64"><pre class="whitespace-pre-wrap">{{ session('backup_output') }}</pre></div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('messages.backup_script') }}</p>
        <p class="mt-2 text-sm font-semibold {{ $scriptExists && $scriptExecutable ? 'text-emerald-600' : 'text-red-600' }}">
            <i class="fas {{ $scriptExists && $scriptExecutable ? 'fa-check-circle' : 'fa-triangle-exclamation' }} me-1"></i>
            {{ $scriptExists ? ($scriptExecutable ? __('messages.backup_ready') : __('messages.backup_not_executable')) : __('messages.backup_missing') }}
        </p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('messages.backup_retention') }}</p>
        <p class="mt-2 text-sm font-semibold text-gray-800">{{ $retentionDays }} {{ __('messages.days') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">{{ __('messages.disk_free') }}</p>
        <p class="mt-2 text-sm font-semibold text-gray-800">{{ $freeSpace !== null ? number_format($freeSpace / 1048576, 0).' MB' : '-' }}</p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500">
            <tr>
                <th class="px-5 py-3 text-start">{{ __('messages.file') }}</th>
                <th class="px-5 py-3 text-start">{{ __('messages.database') }}</th>
                <th class="px-5 py-3 text-start">{{ __('messages.size') }}</th>
                <th class="px-5 py-3 text-start">{{ __('messages.date') }}</th>
                <th class="px-5 py-3 text-end">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($files as $file)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-mono text-xs text-gray-700">{{ $file['name'] }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $file['database'] }}</td>
                <td class="px-5 py-3 text-gray-600">{{ number_format($file['size'] / 1024, 1) }} KB</td>
                <td class="px-5 py-3 text-gray-600">{{ $file['date'] ? \Illuminate\Support\Carbon::createFromTimestamp($file['date'])->format('Y-m-d H:i') : '-' }}</td>
                <td class="px-5 py-3 text-end">
                    <a href="{{ route('settings.backups.download', $file['name']) }}" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-download me-1"></i>{{ __('common.download') ?? __('messages.download') }}
                    </a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">{{ __('messages.no_data') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
