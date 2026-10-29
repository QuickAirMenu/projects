@extends('layouts.app')

@section('title', __('common.dashboard'))

@section('content')
@php
    $kpis = [
        ['key' => 'total_projects',  'value' => $stats['total_projects'],  'icon' => 'fa-briefcase',          'color' => 'blue'],
        ['key' => 'my_tasks',        'value' => $stats['my_tasks'],        'icon' => 'fa-list-check',         'color' => 'indigo'],
        ['key' => 'overdue_projects','value' => $stats['overdue_projects_count'], 'icon' => 'fa-triangle-exclamation', 'color' => 'red'],
    ];
    if ($canViewFinancialStats) {
        $kpis[] = ['key' => 'unpaid_invoices','value' => number_format($financialStats['unpaid_invoices'], 0), 'icon' => 'fa-file-invoice-dollar', 'color' => 'amber'];
    }
@endphp

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('common.dashboard') }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ now()->locale(app()->getLocale())->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-plus text-xs"></i>
                <span class="hidden sm:inline">{{ __('project.add_project') }}</span>
            </a>
            @if($canViewFinancialStats)
            <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-user-plus text-xs"></i>
                <span class="hidden sm:inline">{{ __('client.add_client') }}</span>
            </a>
            @endif
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 gap-4 {{ $canViewFinancialStats ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">
        @foreach($kpis as $kpi)
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-500 truncate">{{ __('common.'.$kpi['key']) }}</p>
                    <p class="text-3xl font-bold text-{{ $kpi['color'] }}-600 mt-2 privacy-mask">
                        @if($kpi['key'] === 'unpaid_invoices')
                            {{ format_currency($financialStats['unpaid_invoices']) }}
                        @else
                            {{ $kpi['value'] }}
                        @endif
                    </p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-{{ $kpi['color'] }}-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas {{ $kpi['icon'] }} text-{{ $kpi['color'] }}-600 text-lg"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Recent Projects --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-briefcase text-blue-600"></i>
                    {{ __('common.recent_projects') }}
                </h2>
                <a href="{{ route('projects.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">
                    {{ __('common.view_all') }} <i class="fas fa-arrow-{{ is_rtl() ? 'left' : 'right' }} text-[10px]"></i>
                </a>
            </div>
            <div>
                @forelse($recentProjects as $project)
                <a href="{{ route('projects.show', $project) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0 transition">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate text-sm">{{ $project->name }}</p>
                        <p class="text-xs text-gray-500 truncate">
                            <i class="fas fa-user text-[10px] text-gray-400"></i>
                            {{ $project->client?->full_name ?? '—' }}
                        </p>
                    </div>
                    <div class="w-24 flex-shrink-0 text-center">
                        @if($project->status)
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold whitespace-nowrap" style="background: {{ $project->status->color }}20; color: {{ $project->status->color }};">
                            {{ $project->status->name }}
                        </span>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 w-28 justify-end">
                        <div class="w-16 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width: {{ $project->progress_percentage }}%"></div>
                        </div>
                        <span class="text-xs font-semibold text-gray-600 w-9 text-end">{{ $project->progress_percentage }}%</span>
                    </div>
                </a>
                @empty
                <div class="px-5 py-12 text-center text-gray-400 text-sm">
                    <i class="fas fa-inbox text-3xl mb-2 block"></i>
                    {{ __('common.no_data') }}
                </div>
                @endforelse
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">

            {{-- My Tasks --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-list-check text-indigo-600"></i>
                        {{ __('common.my_tasks') }}
                    </h2>
                    <a href="{{ route('tasks.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">
                        <i class="fas fa-arrow-{{ is_rtl() ? 'left' : 'right' }} text-[10px]"></i>
                    </a>
                </div>
                <div>
                    @forelse($myTasks as $task)
                    <a href="{{ route('tasks.show', $task) }}" class="flex items-start gap-3 px-5 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0 transition">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 text-sm truncate">{{ $task->title }}</p>
                            <p class="text-xs text-gray-500 truncate mt-0.5 flex items-center gap-1">
                                <i class="fas fa-folder text-gray-400 text-[10px]"></i>
                                <span class="truncate">{{ $task->project?->name ?? '—' }}</span>
                            </p>
                        </div>
                        @if($task->due_date)
                        @php $isOverdue = $task->due_date->isPast(); @endphp
                        <span class="text-[10px] font-semibold whitespace-nowrap {{ $isOverdue ? 'text-red-600' : 'text-gray-500' }}">
                            {{ $task->due_date->format('m/d') }}
                        </span>
                        @endif
                    </a>
                    @empty
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-check-circle text-2xl mb-2 text-green-300 block"></i>
                        {{ __('common.no_my_tasks') }}
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Overdue Projects --}}
            @if($overdueProjects->count())
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-center gap-2 mb-3">
                    <i class="fas fa-triangle-exclamation text-red-600"></i>
                    <h2 class="font-semibold text-red-900">{{ __('common.overdue_projects') }}</h2>
                </div>
                <div class="space-y-2">
                    @foreach($overdueProjects as $op)
                    <a href="{{ route('projects.show', $op) }}" class="block bg-white rounded-lg p-3 hover:shadow-sm transition">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $op->name }}</p>
                        <p class="text-xs text-red-600 mt-1">
                            <i class="fas fa-calendar text-[10px]"></i>
                            {{ $op->deadline->format('Y-m-d') }}
                        </p>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    @if($canViewFinancialStats)
    {{-- Financial Bar --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-2 text-gray-500 text-xs mb-2">
                <i class="fas fa-chart-line text-emerald-500"></i>
                <span>{{ __('common.kpi_total_revenue') }}</span>
            </div>
            <p class="text-xl font-bold text-gray-900 privacy-mask">{{ format_currency($financialStats['total_invoiced']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-2 text-gray-500 text-xs mb-2">
                <i class="fas fa-coins text-blue-500"></i>
                <span>{{ __('common.kpi_total_collected') }}</span>
            </div>
            <p class="text-xl font-bold text-gray-900 privacy-mask">{{ format_currency($financialStats['total_collected']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-2 text-gray-500 text-xs mb-2">
                <i class="fas fa-users text-purple-500"></i>
                <span>{{ __('common.kpi_total_clients') }}</span>
            </div>
            <p class="text-xl font-bold text-gray-900">
                <span class="privacy-mask">{{ $financialStats['total_clients'] }}</span>
                <span class="text-sm font-normal text-gray-400">/ {{ $financialStats['active_clients'] }} {{ __('common.kpi_active_clients') }}</span>
            </p>
        </div>
    </div>
    @endif
</div>
@endsection
