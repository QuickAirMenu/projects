@extends('layouts.app')
@section('title', __('client.edit_client'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('client.edit_client') }}: {{ $client->name }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ __('client.clients') }} / {{ __('client.edit_client') }}</p>
    </div>
    <a href="{{ route('clients.show', $client) }}" class="flex items-center gap-2 text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-xl transition">
        <i class="fas fa-arrow-right"></i> {{ __('messages.back') }}
    </a>
</div>


<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <form action="{{ route('clients.update', $client) }}" method="POST">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.name') }} *</label>
                <input type="text" name="name" value="{{ old('name', $client->name) }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.company_name') }}</label>
                <input type="text" name="company_name" value="{{ old('company_name', $client->company_name) }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('company_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.email') }}</label>
                <input type="email" name="email" value="{{ old('email', $client->email) }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $client->phone) }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.whatsapp') }}</label>
                <input type="text" name="whatsapp" value="{{ old('whatsapp', $client->whatsapp) }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('whatsapp')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.status') }} *</label>
                <select name="status" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['potential','active','vip','inactive'] as $s)
                        <option value="{{ $s }}" {{ old('status', $client->status) == $s ? 'selected' : '' }}>{{ __('client.status_'.$s) }}</option>
                    @endforeach
                </select>
                @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.city') }}</label>
                <input type="text" name="city" value="{{ old('city', $client->city) }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('city')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.industry') }}</label>
                <input type="text" name="industry" value="{{ old('industry', $client->industry) }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('industry')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.notes') }}</label>
                <textarea name="notes" rows="3"
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $client->notes) }}</textarea>
                @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
                <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
            </button>
            <a href="{{ route('clients.show', $client) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition">
                {{ __('messages.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection
