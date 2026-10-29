@extends('portal.layouts.app')

@section('title', __('portal.login_title'))

@section('content')
    <div class="mx-auto max-w-md py-12">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <div class="mb-8 text-center">
                @php $loginLogo = workspace_config('company_logo'); @endphp
                @if($loginLogo)
                    <img src="{{ $loginLogo }}" alt="" class="h-10 mx-auto mb-4">
                @endif
                <h1 class="text-2xl font-bold text-gray-900">{{ __('portal.portal_login') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ __('portal.login_title') }}</p>
            </div>

            <form method="POST" action="{{ route('portal.login.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="email">{{ __('auth.email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        class="form-input w-full"
                        placeholder="name@example.com">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="password">{{ __('auth.password') }}</label>
                    <input id="password" name="password" type="password" required
                        class="form-input w-full"
                        placeholder="********">
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                        {{ __('auth.login') }}
                    </button>
                    <a href="{{ route('portal.forgot-password') }}" class="text-sm text-primary-600 hover:underline">
                        {{ __('portal.forgot_password') }}
                    </a>
                </div>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="text-sm text-primary-600 hover:underline">
                    <i class="fas fa-language me-1"></i>
                    {{ app()->getLocale() === 'ar' ? 'English' : 'عربي' }}
                </a>
            </div>
        </div>
    </div>
@endsection