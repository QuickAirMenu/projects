@extends('portal.layouts.app')

@section('title', $project->name)

@section('breadcrumb')
    <li class="text-gray-400 mx-1">/</li>
    <li class="text-gray-600 font-medium truncate max-w-[200px]">{{ $project->name }}</li>
@endsection

@section('content')
    <div class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $project->name }}</h1>
                <p class="mt-1 text-gray-500">{{ $project->description }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <div class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm">
                    {{ $project->status?->name }}
                </div>
                @if($project->is_handed_off)
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 text-sm font-medium text-emerald-700 flex items-center gap-1.5">
                    <i class="fas fa-check-circle"></i>
                    {{ __('portal.handed_off') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="card rounded-2xl p-6">
                <div class="mb-3 flex items-center justify-between text-sm">
                    <span class="text-gray-500">{{ __('project.progress') }}</span>
                    <span class="font-semibold text-gray-900">{{ $project->progress_percentage ?? 0 }}%</span>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $project->progress_percentage ?? 0 }}%"></div>
                </div>
            </div>

            <div class="card rounded-2xl p-6">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">{{ __('project.workflow') }}</h2>
                <div class="space-y-2">
                    @forelse($project->workflowSteps as $step)
                        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                            <span class="text-gray-700">{{ $step->step_name }}</span>
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $step->completed ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-200 text-gray-600' }}">
                                {{ $step->completed ? __('portal.step_completed') : __('portal.step_pending') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">{{ __('project.no_workflow_steps') }}</p>
                    @endforelse
                </div>
            </div>

            @if($project->attachments->count())
            <div class="card rounded-2xl p-6">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-paperclip text-purple-500"></i> {{ __('portal.attachments') }}
                </h2>
                <div class="space-y-2">
                    @foreach($project->attachments as $attachment)
                        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <i class="fas fa-file text-gray-400"></i>
                                <span class="truncate text-gray-700">{{ $attachment->file_name }}</span>
                                <span class="text-xs text-gray-400">({{ round($attachment->file_size / 1024) }} KB)</span>
                            </div>
                            <a href="{{ route('portal.attachments.download', $attachment) }}" class="shrink-0 p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($showQuotes && $project->quotes->count())
            <div class="card rounded-2xl p-6">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-file-alt text-blue-500"></i> {{ __('finance.quotes') }}
                </h2>
                <div class="space-y-2">
                    @foreach($project->quotes as $quote)
                        @php
                            $sc = ['draft'=>'gray','sent'=>'blue','approved'=>'emerald','rejected'=>'red'][$quote->status] ?? 'gray';
                        @endphp
                        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                            <div>
                                <span class="font-medium text-gray-900">{{ $quote->quote_number }}</span>
                                @if($quote->subject)
                                    <span class="text-gray-500 ms-2 text-sm">- {{ $quote->subject }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-900">{{ number_format($quote->total, 2) }} {{ __('messages.currency') }}</span>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">
                                    {{ __('finance.quote_status.'.$quote->status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($showInvoices && $project->invoices->count())
            <div class="card rounded-2xl p-6">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-file-invoice text-green-500"></i> {{ __('finance.invoices') }}
                </h2>
                <div class="space-y-2">
                    @foreach($project->invoices as $invoice)
                        @php
                            $sc = ['paid'=>'emerald','partially_paid'=>'amber','overdue'=>'red','unpaid'=>'gray'][$invoice->payment_status] ?? 'gray';
                        @endphp
                        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="font-medium text-gray-900">{{ $invoice->invoice_number }}</span>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">
                                    {{ __('finance.status_'.$invoice->payment_status) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-900">{{ number_format($invoice->total, 2) }} {{ __('messages.currency') }}</span>
                                <a href="{{ route('portal.invoices.pdf', $invoice) }}" target="_blank" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition" title="{{ __('messages.download_pdf') }}">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="card rounded-2xl p-6">
                <div class="text-sm text-gray-500">{{ __('project.deadline') }}</div>
                <div class="mt-1 text-lg font-semibold text-gray-900">{{ $project->deadline?->format('Y-m-d') ?? '---' }}</div>
            </div>

            <div class="card rounded-2xl p-6">
                <div class="text-sm text-gray-500">{{ __('common.tasks') }}</div>
                <div class="mt-1 text-lg font-semibold text-gray-900">{{ $project->completed_tasks_count ?? 0 }} / {{ $project->tasks_count ?? 0 }}</div>
            </div>

            <div class="card rounded-2xl p-6">
                <div class="text-sm text-gray-500">{{ __('project.payment_status') }}</div>
                <div class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $paymentStatus === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    <i class="fas fa-circle text-[6px] me-1.5 {{ $paymentStatus === 'paid' ? 'text-emerald-500' : 'text-amber-500' }}"></i>
                    {{ $paymentStatus === 'paid' ? __('finance.paid') : __('portal.payment_pending') }}
                </div>
            </div>

            <div class="card rounded-2xl p-6">
                <div class="text-sm text-gray-500">{{ __('project.team_members') }}</div>
                <div class="mt-3 flex flex-wrap gap-2">
                    @forelse($project->teamMembers as $member)
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700">{{ $member->name }}</span>
                    @empty
                        <span class="text-sm text-gray-400">{{ __('portal.no_team_members') }}</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection