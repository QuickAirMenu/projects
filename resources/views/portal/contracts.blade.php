@extends('portal.layouts.app')

@section('title', __('finance.contracts'))

@section('breadcrumb')
    <li class="text-gray-400 mx-1">/</li>
    <li class="text-gray-600 font-medium">{{ __('finance.contracts') }}</li>
@endsection

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('finance.contracts') }}</h1>
        <p class="mt-1 text-gray-500">{{ __('finance.all_contracts') }}</p>
    </div>

    <div class="card overflow-hidden rounded-2xl">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50 text-left text-gray-500">
                    <th class="px-5 py-4 font-semibold">{{ __('finance.contract_number') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('finance.title') }}</th>
                    <th class="px-5 py-3 font-semibold hidden md:table-cell">{{ __('project.project') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('finance.contract_value') }}</th>
                    <th class="px-5 py-3 font-semibold hidden lg:table-cell">{{ __('finance.period') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('messages.status') }}</th>
                    <th class="px-5 py-3 font-semibold text-end">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($contracts as $contract)
                    @php
                        $statusColors = ['active'=>'emerald','draft'=>'gray','completed'=>'blue','terminated'=>'red'];
                        $sc = $statusColors[$contract->status] ?? 'gray';
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-4 font-medium text-gray-900">{{ $contract->contract_number }}</td>
                        <td class="px-5 py-3 text-gray-700">{{ $contract->title }}</td>
                        <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $contract->project?->name ?? '-' }}</td>
                        <td class="px-5 py-3 font-semibold text-gray-900">{{ number_format($contract->contract_value, 2) }} {{ __('messages.currency') }}</td>
                        <td class="px-5 py-3 text-gray-500 hidden lg:table-cell">
                            @if($contract->start_date && $contract->end_date)
                                {{ $contract->start_date->format('Y-m-d') }} → {{ $contract->end_date->format('Y-m-d') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-3 py-1 text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">
                                {{ __('finance.contract_status.'.$contract->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-end">
                            <a href="{{ route('portal.contracts.pdf', $contract) }}" target="_blank" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg inline-block" title="{{ __('messages.download_pdf') }}">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                            <i class="fas fa-file-contract text-4xl mb-3 block text-gray-300"></i>
                            {{ __('finance.no_contracts') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($contracts->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">{{ $contracts->links() }}</div>
        @endif
    </div>
@endsection