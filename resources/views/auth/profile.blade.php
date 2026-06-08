@extends('layouts.app')
@section('title', __('messages.profile'))

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.profile') }}</h1>
    <p class="text-gray-500 text-sm mt-1">{{ __('messages.profile') }}</p>
</div>


<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Profile Info --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
            <i class="fas fa-user text-blue-500"></i> {{ __('messages.profile') }}
        </h2>
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.name') }}</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.email') }}</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.phone') }}</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.job_title') }}</label>
                    <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.language') }}</label>
                    <select name="language" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="ar" {{ $user->language === 'ar' ? 'selected' : '' }}>العربية</option>
                        <option value="en" {{ $user->language === 'en' ? 'selected' : '' }}>English</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.avatar') }}</label>
                    <input type="file" name="avatar" accept="image/*"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="mt-5">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
                    <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
                </button>
            </div>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
            <i class="fas fa-lock text-orange-500"></i> {{ __('users.change_password') }}
        </h2>
        <form action="{{ route('profile.password.update') }}" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.current_password') }}</label>
                    <input type="password" name="current_password"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.new_password') }}</label>
                    <input type="password" name="password"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.confirm_password') }}</label>
                    <input type="password" name="password_confirmation"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>
            <div class="mt-5">
                <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium px-6 py-2.5 rounded-xl transition">
                    <i class="fas fa-key me-2"></i>{{ __('users.change_password') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
