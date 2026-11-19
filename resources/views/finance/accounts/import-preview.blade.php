@extends('layouts.app')
@section('title', __('accounts.import_preview_title'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('accounts.import_preview_title') }}</h1>
        <p class="text-sm text-gray-500">{{ $account->name }} · {{ $fileName }} · {{ count($rows) }} {{ __('accounts.import_rows') }}</p>
    </div>
    <a href="{{ route('accounts.import.create', $account) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm">{{ __('accounts.import_another') }}</a>
</div>

@if(!empty($parseErrors))
<div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
    <p class="font-semibold mb-1">{{ __('accounts.import_errors') }}</p>
    <ul class="list-disc ps-5">@foreach($parseErrors as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<form action="{{ route('accounts.import.confirm', $account) }}" method="POST">
    @csrf
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500">
                <tr>
                    <th class="px-3 py-3 text-start w-10"><input type="checkbox" onclick="document.querySelectorAll('.imp-row').forEach(c=>{if(!c.disabled)c.checked=this.checked})"></th>
                    <th class="px-3 py-3 text-start">{{ __('messages.date') }}</th>
                    <th class="px-3 py-3 text-start">{{ __('accounts.description') }}</th>
                    <th class="px-3 py-3 text-start">{{ __('accounts.direction') }}</th>
                    <th class="px-3 py-3 text-end">{{ __('accounts.amount') }}</th>
                    <th class="px-3 py-3 text-start">{{ __('accounts.match') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($rows as $i => $row)
                <tr class="{{ !empty($row['duplicate']) ? 'opacity-50' : '' }}">
                    <td class="px-3 py-2"><input class="imp-row" type="checkbox" name="include[]" value="{{ $i }}" checked @disabled(!empty($row['duplicate']))></td>
                    <td class="px-3 py-2 text-gray-700 whitespace-nowrap">{{ $row['date'] }}</td>
                    <td class="px-3 py-2 text-gray-700">{{ $row['description'] }}</td>
                    <td class="px-3 py-2">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $row['direction'] === 'in' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ __('accounts.'.$row['direction']) }}</span>
                    </td>
                    <td class="px-3 py-2 text-end font-medium">{{ number_format($row['amount'], 2) }} {{ $account->currency }}</td>
                    <td class="px-3 py-2 text-xs">
                        @if(!empty($row['duplicate']))
                        <span class="text-gray-400">{{ __('accounts.import_duplicate') }}</span>
                        @elseif($row['matched_id'])
                        <span class="text-emerald-600"><i class="fas fa-link me-1"></i>{{ $row['matched_label'] }}</span>
                        @else
                        <span class="text-blue-600">{{ __('accounts.import_new') }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex justify-end gap-2">
        <a href="{{ route('accounts.show', $account) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm">{{ __('messages.cancel') }}</a>
        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium">
            <i class="fas fa-file-import me-2"></i>{{ __('accounts.import_confirm') }}
        </button>
    </div>
</form>
@endsection
