@extends('layouts.app')

@section('title', __('common.dashboard'))

@section('page-header')
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-home {{ is_rtl() ? 'ml-3' : 'mr-3' }} text-primary-600"></i>
            {{ __('common.dashboard') }}
        </h1>
        <p class="text-gray-600">
            <i class="fas fa-calendar {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
            {{ now()->locale(app()->getLocale())->translatedFormat('l، d F Y') }}
        </p>
    </div>
@endsection

@section('content')
    @if($stats['total_projects'] == 0 && (!isset($financialStats['total_clients']) || $financialStats['total_clients'] == 0))
    <div x-data="{ show: true }" x-show="show" class="mb-6 bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-xl font-bold mb-2">{{ __('dashboard.welcome_onboard', ['app' => config('app.name')]) }}</h2>
                <p class="text-blue-100 mb-4">{{ __('dashboard.get_started') }}</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('clients.create') }}" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-xl text-sm font-medium transition">
                        <i class="fas fa-user-plus me-2"></i>{{ __('dashboard.create_first_client') }}
                    </a>
                    <a href="{{ route('projects.create') }}" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-xl text-sm font-medium transition">
                        <i class="fas fa-briefcase me-2"></i>{{ __('dashboard.create_first_project') }}
                    </a>
                    <a href="{{ route('users.create') }}" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-xl text-sm font-medium transition">
                        <i class="fas fa-user-plus me-2"></i>{{ __('dashboard.invite_team') }}
                    </a>
                </div>
            </div>
            <button @click="show = false" class="text-blue-200 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Projects -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">{{ __('common.total_projects') }}</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_projects'] }}</p>
                        <p class="text-sm text-green-600 mt-2">
                            <i class="fas fa-arrow-up {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>
                            {{ __('common.active_count', ['count' => $stats['active_projects']]) }}
                        </p>
                    </div>
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-briefcase text-3xl text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Tasks -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">{{ __('common.my_tasks') }}</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['my_tasks'] }}</p>
                        <p class="text-sm text-yellow-600 mt-2">
                            <i class="fas fa-exclamation-triangle {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>
                            {{ __('common.overdue_count', ['count' => $stats['overdue_tasks']]) }}
                        </p>
                    </div>
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-tasks text-3xl text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        @if($canViewFinancialStats)
        <!-- Clients -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">{{ __('common.total_clients') }}</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $financialStats['total_clients'] }}</p>
                        <p class="text-sm text-green-600 mt-2">
                            <i class="fas fa-check-circle {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>
                            {{ __('common.active_count', ['count' => $financialStats['active_clients']]) }}
                        </p>
                    </div>
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-3xl text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unpaid Invoices -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">{{ __('common.unpaid_invoices') }}</p>
                        <p class="text-3xl font-bold text-gray-900">{{ format_currency($financialStats['unpaid_invoices']) }}</p>
                        <p class="text-sm text-gray-500 mt-2">
                            <i class="fas fa-clock {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>
                            {{ __('common.pending_payment') }}
                        </p>
                    </div>
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-file-invoice-dollar text-3xl text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Projects -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-briefcase {{ is_rtl() ? 'ml-2' : 'mr-2' }} text-primary-600"></i>
                        {{ __('common.recent_projects') }}
                    </h2>
                    <a href="{{ route('projects.index') }}" class="text-sm text-primary-600 hover:text-primary-700">
                        {{ __('common.view_all') }}
                        <i class="fas fa-arrow-{{ is_rtl() ? 'left' : 'right' }} {{ is_rtl() ? 'mr-1' : 'ml-1' }}"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('common.project') }}</th>
                                    <th>{{ __('common.client') }}</th>
                                    <th>{{ __('common.status') }}</th>
                                    <th>{{ __('common.manager') }}</th>
                                    <th>{{ __('common.progress') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProjects as $project)
                                <tr>
                                    <td>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $project->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $project->project_number }}</p>
                                        </div>
                                    </td>
                                    <td>{{ $project->client?->name }}</td>
                                    <td>{{ $project->status?->name_ar }}</td>
                                    <td>
                                        <div class="flex items-center">
                                            <img src="{{ $project->manager?->avatar_url }}" alt="{{ $project->manager?->name }}" class="w-8 h-8 rounded-full {{ is_rtl() ? 'ml-2' : 'mr-2' }}">
                                            <span class="text-sm">{{ $project->manager?->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2 {{ is_rtl() ? 'ml-3' : 'mr-3' }}">
                                                <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $project->progress_percentage }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium text-gray-700">{{ $project->progress_percentage }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-3"></i>
                                        <p>{{ __('common.no_projects') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Tasks -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-tasks {{ is_rtl() ? 'ml-2' : 'mr-2' }} text-primary-600"></i>
                        {{ __('common.my_tasks') }}
                    </h2>
                </div>
                <div class="card-body p-0">
                    <div class="max-h-96 overflow-y-auto custom-scrollbar">
                        @forelse($myTasks as $task)
                        <div class="p-4 border-b border-gray-100 hover:bg-gray-50">
                            <div class="flex items-start justify-between mb-2">
                                <p class="font-medium text-gray-900">{{ $task->title }}</p>
                                {!! $task->priority_badge !!}
                            </div>
                            <p class="text-sm text-gray-600 mb-2">
                                <i class="fas fa-briefcase {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>
                                {{ $task->project->name }}
                            </p>
                            @if($task->due_date)
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-calendar {{ is_rtl() ? 'ml-1' : 'mr-1' }}"></i>
                                {{ $task->due_date->format('Y-m-d') }}
                            </p>
                            @endif
                        </div>
                        @empty
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-check-circle text-4xl mb-3"></i>
                            <p>{{ __('common.no_pending_tasks') }}</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
