@extends('layouts.app')
@section('title', __('project.handoff_project'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('project.handoff_project') }}</h1>
        <p class="text-gray-500 text-sm">{{ $project->name }} - {{ $project->client?->full_name }}</p>
    </div>
    <a href="{{ route('projects.show', $project) }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm">{{ __('messages.back') }}</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        {{-- Project Details --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold mb-3 text-gray-800">{{ __('project.project') }}</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-gray-500">{{ __('finance.total') }} ({{ __('messages.currency') }})</p><p class="font-semibold">{{ number_format($project->budget, 2) }}</p></div>
                <div><p class="text-gray-500">{{ __('project.actual_cost') }}</p><p class="font-semibold">{{ number_format($project->actual_cost, 2) }}</p></div>
                <div><p class="text-gray-500">{{ __('project.progress') }}</p><p class="font-semibold">{{ $project->progress_percentage }}%</p></div>
                <div><p class="text-gray-500">{{ __('project.handoff_date') }}</p><p class="font-semibold">{{ $project->handed_off_at?->format('Y-m-d H:i') ?? '-' }}</p></div>
            </div>
        </div>

        {{-- Original Quote --}}
        @if($project->quotes->count())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold mb-3 text-gray-800"><i class="fas fa-file-alt text-blue-600 me-2"></i>{{ __('finance.quote_number') }}</h3>
            <div class="space-y-2">
                @foreach($project->quotes as $quote)
                <div class="flex items-center justify-between border rounded-xl p-3 text-sm">
                    <div>
                        <span class="font-medium text-gray-800">{{ $quote->quote_number }}</span>
                        @if($quote->subject)
                            <span class="text-gray-500 ms-2">- {{ $quote->subject }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold">{{ number_format($quote->total, 2) }} {{ __('messages.currency') }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ ['draft'=>'gray','sent'=>'blue','approved'=>'green','rejected'=>'red'][$quote->status] ?? 'gray' }}-100 text-{{ ['draft'=>'gray','sent'=>'blue','approved'=>'green','rejected'=>'red'][$quote->status] ?? 'gray' }}-700">
                            {{ __('finance.quote_status.'.$quote->status) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Invoices --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold mb-3 text-gray-800"><i class="fas fa-file-invoice text-green-600 me-2"></i>{{ __('finance.invoices') }}</h3>
            <div class="space-y-2">
                @forelse($project->invoices as $invoice)
                    <div class="flex items-center justify-between border rounded-xl p-3 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-800">{{ $invoice->invoice_number }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ ['paid'=>'green','partially_paid'=>'yellow','overdue'=>'red','unpaid'=>'gray'][$invoice->payment_status] ?? 'gray' }}-100 text-{{ ['paid'=>'green','partially_paid'=>'yellow','overdue'=>'red','unpaid'=>'gray'][$invoice->payment_status] ?? 'gray' }}-700">
                                {{ __('finance.payment_status.'.$invoice->payment_status) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold">{{ number_format($invoice->total, 2) }} {{ __('messages.currency') }}</span>
                            <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="{{ __('common.download_pdf') }}">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">{{ __('finance.no_invoices') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Attachments --}}
        @if($project->attachments->count())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold mb-3 text-gray-800"><i class="fas fa-paperclip text-purple-600 me-2"></i>{{ __('project.attachments') }}</h3>
            <div class="space-y-2">
                @foreach($project->attachments as $attachment)
                    <div class="flex items-center justify-between border rounded-xl p-3 text-sm">
                        <div class="flex items-center gap-2 min-w-0">
                            <i class="fas fa-file text-gray-400"></i>
                            <span class="truncate text-gray-800">{{ $attachment->file_name }}</span>
                            <span class="text-xs text-gray-400">({{ round($attachment->file_size / 1024) }} KB)</span>
                        </div>
                        <a href="{{ route('projects.attachments.download', [$project, $attachment]) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg flex-shrink-0">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Handoff Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 h-fit">
        <form action="{{ route('projects.handoff.complete', $project) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('project.handoff_notes') }}</label>
                <textarea name="handoff_notes" rows="5" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm">{{ old('handoff_notes') }}</textarea>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-sm">
                <i class="fas fa-check-circle me-1"></i>{{ __('project.handoff_project') }}
            </button>
        </form>
    </div>
</div>
@endsection
