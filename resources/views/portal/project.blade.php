@extends('portal.layouts.app')

@section('title', $project->name)

@section('content')
    <div class="mb-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">{{ $project->name }}</h1>
                <p class="mt-2 max-w-3xl text-slate-400">{{ $project->description }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-300">
                {{ $project->status?->name }}
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <div class="mb-4 flex items-center justify-between text-sm text-slate-400">
                    <span>Progress</span>
                    <span>{{ $project->progress_percentage ?? 0 }}%</span>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-emerald-400" style="width: {{ $project->progress_percentage ?? 0 }}%"></div>
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <h2 class="mb-4 text-xl font-semibold text-white">خطوات سير العمل</h2>
                <div class="space-y-3">
                    @foreach($project->workflowSteps as $step)
                        <div class="flex items-center justify-between rounded-2xl bg-slate-900/70 px-4 py-3">
                            <span class="text-slate-200">{{ $step->step_name }}</span>
                            <span class="rounded-full px-3 py-1 text-xs {{ $step->completed ? 'bg-emerald-400/10 text-emerald-200' : 'bg-slate-800 text-slate-300' }}">
                                {{ $step->completed ? 'مكتملة' : 'غير مكتملة' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <div class="text-sm text-slate-400">Deadline</div>
                <div class="mt-2 text-lg font-semibold text-white">{{ $project->deadline?->format('Y-m-d') ?? '---' }}</div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <div class="text-sm text-slate-400">المهام</div>
                <div class="mt-2 text-lg font-semibold text-white">{{ $project->completed_tasks_count ?? 0 }} / {{ $project->tasks_count ?? 0 }}</div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <div class="text-sm text-slate-400">حالة الدفع</div>
                <div class="mt-2 inline-flex rounded-full px-3 py-1 text-sm {{ $paymentStatus === 'paid' ? 'bg-emerald-400/10 text-emerald-200' : 'bg-amber-400/10 text-amber-200' }}">
                    {{ $paymentStatus === 'paid' ? 'paid' : 'pending' }}
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <div class="text-sm text-slate-400">فريق العمل</div>
                <div class="mt-3 flex flex-wrap gap-2">
                    @forelse($project->teamMembers as $member)
                        <span class="rounded-full bg-slate-800 px-3 py-1 text-sm text-slate-200">{{ $member->name }}</span>
                    @empty
                        <span class="text-slate-500">لا يوجد أعضاء ظاهرون</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
