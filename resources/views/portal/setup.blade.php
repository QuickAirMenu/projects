@extends('portal.layouts.app')

@section('title', 'Setup')

@section('content')
    <div class="mx-auto max-w-md py-12">
        <div class="rounded-3xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-white">استكمال الحساب</h1>
                <p class="mt-2 text-sm text-slate-400">{{ $account->client_name }} / {{ $account->email }}</p>
            </div>

            <form method="POST" action="{{ route('portal.setup.post', $token) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="name">Name</label>
                    <input id="name" type="text" value="{{ $account->client_name }}" disabled
                        class="w-full rounded-xl border border-white/10 bg-slate-800 px-4 py-3 text-slate-300">
                </div>

                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="password">Password</label>
                    <input id="password" name="password" type="password" required minlength="8"
                        class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white placeholder-slate-500 outline-none ring-0 focus:border-emerald-400"
                        placeholder="********">
                </div>

                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                        class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white placeholder-slate-500 outline-none ring-0 focus:border-emerald-400"
                        placeholder="********">
                </div>

                <button type="submit" class="w-full rounded-xl bg-emerald-500 px-4 py-3 font-semibold text-slate-950 hover:bg-emerald-400">
                    حفظ كلمة المرور
                </button>
            </form>
        </div>
    </div>
@endsection
