@extends('portal.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">{{ $client->name }}</h1>
        <p class="mt-2 text-slate-400">مشاريعك النشطة فقط</p>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse($client->projects as $project)
            <a href="{{ route('portal.projects.show', $project) }}" class="rounded-3xl border border-white/10 bg-white/5 p-6 transition hover:border-emerald-400/40 hover:bg-white/10">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-white">{{ $project->name }}</h2>
                        <p class="mt-1 text-sm text-slate-400">{{ $project->status?->name }}</p>
                    </div>
                    <span class="rounded-full bg-emerald-400/10 px-3 py-1 text-sm text-emerald-200">{{ $project->progress_percentage ?? 0 }}%</span>
                </div>

                <div class="mb-4 h-2 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-emerald-400" style="width: {{ $project->progress_percentage ?? 0 }}%"></div>
                </div>

                <div class="flex items-center justify-between text-sm text-slate-400">
                    <span>Deadline</span>
                    <span>{{ $project->deadline?->format('Y-m-d') ?? '---' }}</span>
                </div>
            </a>
        @empty
            <div class="rounded-3xl border border-white/10 bg-white/5 p-8 text-slate-300">
                لا توجد مشاريع نشطة حالياً.
            </div>
        @endforelse
    </div>
@endsection
