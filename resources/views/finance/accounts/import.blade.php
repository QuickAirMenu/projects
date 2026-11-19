@extends('layouts.app')
@section('title', __('accounts.import_title'))

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('accounts.show', $account) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm"><i class="fas fa-arrow-{{ is_rtl() ? 'right' : 'left' }}"></i></a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('accounts.import_title') }}</h1>
        <p class="text-sm text-gray-500">{{ $account->name }} · {{ $account->currency }}</p>
    </div>
</div>

<form action="{{ route('accounts.import.preview', $account) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4 max-w-2xl">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.import_file') }}</label>
        <input type="file" name="file" required accept=".csv,.txt" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm">
        @error('file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        <p class="mt-2 text-xs text-gray-500">{{ __('accounts.import_hint') }}</p>
    </div>
    <div class="rounded-xl bg-gray-50 p-4 text-xs text-gray-600 space-y-1">
        <p>{{ __('accounts.import_cols') }}</p>
        <p class="font-mono text-[11px]">Date, Description, Amount</p>
        <p class="font-mono text-[11px]">2026-11-19, Client transfer, 1000.00</p>
        <p class="font-mono text-[11px]">2026-11-19, Team payout, (225.00)</p>
    </div>
    <div class="flex justify-end gap-2">
        <a href="{{ route('accounts.show', $account) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm">{{ __('messages.cancel') }}</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium">{{ __('accounts.import_preview') }}</button>
    </div>
</form>
@endsection
