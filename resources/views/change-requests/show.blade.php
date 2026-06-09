@extends('layouts.app')
@section('title', $changeRequest->request_number)

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $changeRequest->title }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $changeRequest->request_number }} · {{ $changeRequest->project?->name }}</p>
    </div>
    <a href="{{ route('change-requests.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm transition">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>


@php
    $statusColors = ['pending'=>'yellow','approved'=>'green','rejected'=>'red','implemented'=>'blue'];
    $sc = $statusColors[$changeRequest->status] ?? 'gray';
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-5">
        {{-- Details Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-700 mb-4">{{ __('project.request_description') }}</h3>
            <p class="text-gray-600 leading-relaxed">{{ $changeRequest->description }}</p>

            @if($changeRequest->impact_analysis)
            <div class="mt-5 pt-5 border-t">
                <h3 class="font-semibold text-gray-700 mb-2">{{ __('project.impact_analysis') }}</h3>
                <p class="text-gray-600 leading-relaxed">{{ $changeRequest->impact_analysis }}</p>
            </div>
            @endif

            @if($changeRequest->rejection_reason)
            <div class="mt-5 p-4 bg-red-50 rounded-xl border border-red-100">
                <p class="text-sm font-semibold text-red-700 mb-1">{{ __('project.rejection_reason') }}</p>
                <p class="text-sm text-red-600">{{ $changeRequest->rejection_reason }}</p>
            </div>
            @endif
        </div>

        {{-- Actions --}}
        @if($changeRequest->status === 'pending')
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 mb-4">{{ __('project.approval_decision') }}</h3>
            <div class="flex gap-3">
                <form action="{{ route('change-requests.approve', $changeRequest) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl font-medium transition">
                        <i class="fas fa-check me-2"></i>{{ __('project.approve_request') }}
                    </button>
                </form>
                <button onclick="document.getElementById('reject-form').classList.toggle('hidden')"
                        class="bg-red-500 hover:bg-red-600 text-white px-6 py-2.5 rounded-xl font-medium transition">
                    <i class="fas fa-times me-2"></i>{{ __('project.reject_request') }}
                </button>
            </div>
            <form id="reject-form" action="{{ route('change-requests.reject', $changeRequest) }}" method="POST" class="hidden mt-4">
                @csrf
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('project.rejection_reason_label') }} *</label>
                <textarea name="rejection_reason" rows="3" required
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 mb-3"></textarea>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-xl text-sm transition">{{ __('project.confirm_rejection') }}</button>
            </form>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-sm space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-500">{{ __('project.status') }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ __('project.status_'.$changeRequest->status) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">{{ __('project.source_label') }}</span>
                <span class="font-medium">{{ $changeRequest->requested_by === 'client' ? __('project.from_client') : __('project.internal') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">{{ __('project.scope') }}</span>
                <span class="font-medium {{ $changeRequest->scope_type === 'out_of_scope' ? 'text-orange-600' : 'text-green-600' }}">
                    {{ __('project.scope_'.$changeRequest->scope_type) }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">{{ __('project.priority') }}</span>
                <span class="font-medium">{{ __('project.priority_'.$changeRequest->priority) }}</span>
            </div>
            @if($changeRequest->additional_cost > 0)
            <div class="flex justify-between border-t pt-3">
                <span class="text-gray-500">{{ __('project.additional_cost') }}</span>
                <span class="font-bold text-orange-600">{{ number_format($changeRequest->additional_cost, 2) }} {{ __('messages.currency') }}</span>
            </div>
            @endif
            @if($changeRequest->additional_hours > 0)
            <div class="flex justify-between">
                <span class="text-gray-500">{{ __('project.additional_hours') }}</span>
                <span class="font-medium">{{ $changeRequest->additional_hours }}h</span>
            </div>
            @endif
            <div class="border-t pt-3">
                <p class="text-gray-500 mb-1">{{ __('project.project') }}</p>
                <a href="{{ route('projects.show', $changeRequest->project) }}" class="text-blue-600 hover:underline text-sm">{{ $changeRequest->project?->name }}</a>
            </div>
            <div>
                <p class="text-gray-500 mb-1">{{ __('messages.created_by') }}</p>
                <p class="font-medium">{{ $changeRequest->creator?->name }}</p>
                <p class="text-xs text-gray-400">{{ $changeRequest->created_at->format('Y-m-d H:i') }}</p>
            </div>
            @if($changeRequest->approver)
            <div class="border-t pt-3">
                <p class="text-gray-500 mb-1">{{ $changeRequest->status === 'approved' ? __('project.approved_by') : __('project.rejected_by') }}</p>
                <p class="font-medium">{{ $changeRequest->approver->name }}</p>
                <p class="text-xs text-gray-400">{{ $changeRequest->approved_at?->format('Y-m-d H:i') }}</p>
            </div>
            @endif
        </div>

        <form action="{{ route('change-requests.destroy', $changeRequest) }}" method="POST" onsubmit="return confirmDelete(this)" class="inline">
            @csrf @method('DELETE')
            <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-xl text-sm">
                <i class="fas fa-trash me-1"></i>{{ __('project.delete_request') }}
            </button>
        </form>
    </div>
</div>
@endsection
