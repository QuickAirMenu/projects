@extends('layouts.app')
@section('title', __('settings.api_tokens'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('settings.api_tokens') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('messages.api_tokens_hint') }}</p>
    </div>
    <a href="{{ route('api.docs') }}" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm">
        <i class="fas fa-book me-2"></i>{{ __('messages.api_docs') }}
    </a>
</div>

@if(session('plain_text_token'))
<div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">
    <p class="text-sm font-semibold text-amber-800">{{ __('messages.api_token_once') }}</p>
    <div class="mt-3 flex items-center gap-2">
        <input id="plainToken" readonly value="{{ session('plain_text_token') }}" class="w-full rounded-xl border border-amber-300 bg-white px-3 py-2 font-mono text-xs" onclick="this.select()">
        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('plainToken').value); this.textContent='✓'" class="rounded-xl bg-amber-600 px-4 py-2 text-sm text-white">{{ __('messages.copy') }}</button>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 h-fit">
        <h2 class="text-sm font-semibold text-gray-800 mb-4">{{ __('messages.create_token') }}</h2>
        <form action="{{ route('api-tokens.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('messages.token_name') }}</label>
                <input name="name" required maxlength="60" value="{{ old('name') }}" placeholder="{{ __('messages.token_name_placeholder') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                <i class="fas fa-plus me-2"></i>{{ __('messages.create') }}
            </button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500">
                <tr>
                    <th class="px-5 py-3 text-start">{{ __('messages.token_name') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('messages.token_created') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('messages.token_last_used') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('messages.token_expires') }}</th>
                    <th class="px-5 py-3 text-end">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tokens as $token)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $token->name }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $token->created_at?->format('Y-m-d') }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $token->last_used_at?->format('Y-m-d H:i') ?? '-' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $token->expires_at?->format('Y-m-d') ?? '-' }}</td>
                    <td class="px-5 py-3 text-end">
                        <form action="{{ route('api-tokens.destroy', $token->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:text-red-800 text-xs"><i class="fas fa-trash me-1"></i>{{ __('messages.revoke') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">{{ __('messages.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
