@extends('portal.layouts.app')

@section('title', __('portal.my_account'))

@section('breadcrumb')
    <li class="text-gray-400 mx-1">/</li>
    <li class="text-gray-600 font-medium">{{ __('portal.my_account') }}</li>
@endsection

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('portal.my_account') }}</h1>
        <p class="mt-1 text-gray-500">{{ __('portal.profile_subtitle') }}</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="card rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('portal.personal_info') }}</h2>
            <form method="POST" action="{{ route('portal.profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="name">{{ __('users.name') }}</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $client->name) }}" required
                        class="form-input w-full">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="email">{{ __('auth.email') }}</label>
                    <input id="email" type="email" value="{{ $client->email }}" disabled
                        class="form-input w-full bg-gray-50 text-gray-500 cursor-not-allowed">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="phone">{{ __('client.phone') }}</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $client->phone) }}"
                        class="form-input w-full">
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                    {{ __('messages.save') }}
                </button>
            </form>
        </div>

        <div class="card rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('portal.change_password') }}</h2>
            <form method="POST" action="{{ route('portal.profile.password') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="current_password">{{ __('portal.current_password') }}</label>
                    <input id="current_password" name="current_password" type="password" required
                        class="form-input w-full">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="password">{{ __('portal.new_password') }}</label>
                    <input id="password" name="password" type="password" required minlength="8"
                        class="form-input w-full">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="password_confirmation">{{ __('portal.confirm_password') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                        class="form-input w-full">
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-key {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                    {{ __('portal.update_password') }}
                </button>
            </form>
        </div>
    </div>
@endsection