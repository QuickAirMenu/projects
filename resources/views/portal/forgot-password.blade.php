@extends('portal.layouts.app')

@section('title', __('portal.forgot_password_title'))

@section('content')
    <div class="mx-auto max-w-md py-12">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <div class="mb-8 text-center">
                @php $fpLogo = workspace_config('company_logo'); @endphp
                @if($fpLogo)
                    <img src="{{ $fpLogo }}" alt="" class="h-10 mx-auto mb-4">
                @endif
                <h1 class="text-2xl font-bold text-gray-900">{{ __('portal.forgot_password_title') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ __('portal.forgot_password_subtitle') }}</p>
            </div>

            <form method="POST" action="{{ route('portal.forgot-password.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="email">{{ __('auth.email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        class="form-input w-full"
                        placeholder="name@example.com">
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <i class="fas fa-paper-plane {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                    {{ __('portal.send_reset_link') }}
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('portal.login') }}" class="text-sm text-primary-600 hover:underline">
                    <i class="fas fa-arrow-{{ is_rtl() ? 'right' : 'left' }} me-1"></i>
                    {{ __('portal.back_to_login') }}
                </a>
            </div>
        </div>
    </div>
@endsection