@extends('layouts.app')
@section('title', __('client.add_client'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('client.add_client') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ __('client.clients') }} / {{ __('client.add_client') }}</p>
    </div>
    <a href="{{ route('clients.index') }}" class="flex items-center gap-2 text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-xl transition">
        <i class="fas fa-arrow-right"></i> {{ __('messages.back') }}
    </a>
</div>


<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <form action="{{ route('clients.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.name') }} <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.company_name') }}</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.whatsapp') }}</label>
                <input type="text" name="whatsapp" value="{{ old('whatsapp') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.status') }} <span class="text-red-500">*</span></label>
                <select name="status" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="potential" {{ old('status') == 'potential' ? 'selected' : '' }}>{{ __('client.status_potential') }}</option>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>{{ __('client.status_active') }}</option>
                    <option value="vip" {{ old('status') == 'vip' ? 'selected' : '' }}>{{ __('client.status_vip') }}</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>{{ __('client.status_inactive') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.city') }}</label>
                <input type="text" name="city" value="{{ old('city') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.industry') }}</label>
                <input type="text" name="industry" value="{{ old('industry') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.source') }}</label>
                <select name="source" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- {{ __('messages.select') }} --</option>
                    <option value="referral">Referral</option>
                    <option value="website">Website</option>
                    <option value="social_media">Social Media</option>
                    <option value="exhibition">Exhibition</option>
                    <option value="cold_call">Cold Call</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.assigned_to') }}</label>
                <select name="assigned_to" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- {{ __('messages.select') }} --</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('client.notes') }}</label>
                <textarea name="notes" rows="3"
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
                <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
            </button>
            <a href="{{ route('clients.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-xl transition">
                {{ __('messages.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection
