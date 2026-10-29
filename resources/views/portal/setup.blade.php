@extends('portal.layouts.app')

@section('title', __('portal.setup_title'))

@section('content')
    <div class="mx-auto max-w-md py-12">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <div class="mb-8 text-center">
                @php $setupLogo = workspace_config('company_logo'); @endphp
                @if($setupLogo)
                    <img src="{{ $setupLogo }}" alt="" class="h-10 mx-auto mb-4">
                @endif
                <h1 class="text-2xl font-bold text-gray-900">{{ __('portal.setup_title') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $account->client_name }} / {{ $account->email }}</p>
            </div>

            <form method="POST" action="{{ route('portal.setup.post', $token) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="name">{{ __('common.client') }}</label>
                    <input id="name" type="text" value="{{ $account->client_name }}" disabled
                        class="form-input w-full bg-gray-50 text-gray-500">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="password">{{ __('auth.password') }}</label>
                    <input id="password" name="password" type="password" required minlength="8"
                        class="form-input w-full"
                        placeholder="********">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="password_confirmation">{{ __('portal.confirm_password') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                        class="form-input w-full"
                        placeholder="********">
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <i class="fas fa-check-circle {{ is_rtl() ? 'ml-2' : 'mr-2' }}"></i>
                    {{ __('portal.setup_btn') }}
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('portal.login') }}" class="text-sm text-gray-500 hover:text-gray-700">
                    <i class="fas fa-arrow-{{ is_rtl() ? 'right' : 'left' }} me-1"></i>
                    {{ __('portal.back_to_login') }}
                </a>
            </div>
        </div>
    </div>
@endsection