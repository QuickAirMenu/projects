@extends('portal.layouts.app')

@section('title', __('common.dashboard'))

@section('breadcrumb')
    <li class="text-gray-400 mx-1">/</li>
    <li class="text-gray-600 font-medium">{{ __('common.dashboard') }}</li>
@endsection

@section('content')
    <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $client->name }}</h1>
            <p class="mt-1 text-gray-500">{{ __('portal.active_projects_only') }}</p>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach([
            ['label' => __('portal.outstanding'), 'value' => number_format($stats['outstanding'], 2), 'icon' => 'fa-file-invoice-dollar', 'tone' => 'red'],
            ['label' => __('portal.total_paid'), 'value' => number_format($stats['paid'], 2), 'icon' => 'fa-money-bill-wave', 'tone' => 'emerald'],
            ['label' => __('portal.pending_quotes'), 'value' => $stats['pending_quotes'], 'icon' => 'fa-file-signature', 'tone' => 'amber'],
            ['label' => __('portal.active_projects'), 'value' => $stats['active_projects'], 'icon' => 'fa-briefcase', 'tone' => 'blue'],
        ] as $kpi)
            <div class="card rounded-2xl p-5">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $kpi['tone'] }}-100">
                    <i class="fas {{ $kpi['icon'] }} text-{{ $kpi['tone'] }}-600"></i>
                </div>
                <p class="text-lg font-bold text-gray-900">{{ $kpi['value'] }} <span class="text-xs font-medium text-gray-400">{{ __('messages.currency') }}</span></p>
                <p class="mt-1 text-xs text-gray-500">{{ $kpi['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Projects --}}
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('portal.projects') }}</h2>
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
            <div class="card col-span-full rounded-2xl p-8 text-center text-gray-400">
                <i class="fas fa-folder-open mb-3 block text-4xl text-gray-300"></i>
                {{ __('portal.no_projects') }}
            </div>
        @endforelse
    </div>

    {{-- Recent invoices + quotes --}}
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="card rounded-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h2 class="font-semibold text-gray-900"><i class="fas fa-file-invoice me-2 text-blue-600"></i>{{ __('portal.recent_invoices') }}</h2>
                <a href="{{ route('portal.invoices') }}" class="text-xs font-medium text-primary-600 hover:underline">{{ __('portal.view_all') }}</a>
            </div>
            @forelse($recentInvoices as $invoice)
                @php
                    $invColors = ['paid'=>'emerald','partially_paid'=>'amber','unpaid'=>'red','overdue'=>'orange'];
                    $ic = $invColors[$invoice->payment_status] ?? 'gray';
                @endphp
                <a href="{{ route('portal.invoices') }}" class="flex items-center justify-between border-b border-gray-100 px-5 py-3 text-sm last:border-0 hover:bg-gray-50">
                    <div>
                        <p class="font-medium text-gray-800">{{ $invoice->invoice_number }}</p>
                        <p class="text-xs text-gray-400">{{ $invoice->invoice_date?->format('Y-m-d') }}</p>
                    </div>
                    <div class="text-end">
                        <p class="font-semibold text-gray-800">{{ number_format($invoice->total, 2) }} <span class="text-xs text-gray-400">{{ __('messages.currency') }}</span></p>
                        <span class="rounded-full bg-{{ $ic }}-100 px-2 py-0.5 text-[10px] font-medium text-{{ $ic }}-700">{{ __('messages.'.$invoice->payment_status) }}</span>
                    </div>
                </a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-gray-400">{{ __('portal.no_invoices') }}</p>
            @endforelse
        </div>

        <div class="card rounded-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h2 class="font-semibold text-gray-900"><i class="fas fa-file-signature me-2 text-amber-600"></i>{{ __('portal.recent_quotes') }}</h2>
                <a href="{{ route('portal.quotes') }}" class="text-xs font-medium text-primary-600 hover:underline">{{ __('portal.view_all') }}</a>
            </div>
            @forelse($recentQuotes as $quote)
                @php
                    $qColors = ['draft'=>'gray','sent'=>'blue','approved'=>'emerald','rejected'=>'red','expired'=>'amber'];
                    $qc = $qColors[$quote->status] ?? 'gray';
                @endphp
                <a href="{{ route('portal.quotes') }}" class="flex items-center justify-between border-b border-gray-100 px-5 py-3 text-sm last:border-0 hover:bg-gray-50">
                    <div class="min-w-0">
                        <p class="truncate font-medium text-gray-800">{{ $quote->quote_number }}</p>
                        <p class="truncate text-xs text-gray-400">{{ $quote->subject }}</p>
                    </div>
                    <div class="text-end">
                        <p class="font-semibold text-gray-800">{{ number_format($quote->total, 2) }} <span class="text-xs text-gray-400">{{ __('messages.currency') }}</span></p>
                        <span class="rounded-full bg-{{ $qc }}-100 px-2 py-0.5 text-[10px] font-medium text-{{ $qc }}-700">{{ $quote->status }}</span>
                    </div>
                </a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-gray-400">{{ __('portal.no_quotes') }}</p>
            @endforelse
        </div>
    </div>
@endsection
