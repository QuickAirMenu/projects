@extends('portal.layouts.app')

@section('title', __('common.dashboard'))

@section('breadcrumb')
    <li class="text-gray-400 mx-1">/</li>
    <li class="text-gray-600 font-medium">{{ __('common.dashboard') }}</li>
@endsection

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ $client->name }}</h1>
        <p class="mt-1 text-gray-500">{{ __('portal.active_projects_only') }}</p>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse($client->projects as $project)
            <a href="{{ route('portal.projects.show', $project) }}" class="card rounded-2xl p-6 transition hover:border-primary-200 hover:shadow-md">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $project->name }}</h2>
                        @php
                            $statusColors = ['new-request'=>'blue','under-analysis'=>'purple','waiting-approval'=>'amber','approved'=>'emerald','in-progress'=>'blue','internal-review'=>'violet','sent-to-client'=>'indigo','revisions'=>'orange','completed'=>'emerald','closed'=>'gray','on-hold'=>'amber','cancelled'=>'red'];
                            $sc = $statusColors[$project->status?->slug] ?? 'gray';
                        @endphp
                        <p class="mt-1 text-sm">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ $project->status?->name }}</span>
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700">{{ $project->progress_percentage ?? 0 }}%</span>
                </div>

                <div class="mb-4 h-2 overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $project->progress_percentage ?? 0 }}%"></div>
                </div>

                <div class="flex items-center justify-between text-sm text-gray-500">
                    <span>{{ __('project.deadline') }}</span>
                    <span>{{ $project->deadline?->format('Y-m-d') ?? '---' }}</span>
                </div>
            </a>
        @empty
            <div class="card rounded-2xl p-8 text-center text-gray-400 col-span-full">
                <i class="fas fa-folder-open text-4xl mb-3 block text-gray-300"></i>
                {{ __('portal.no_projects') }}
            </div>
        @endforelse
    </div>
@endsection
