@extends('portal.layouts.app')

@section('title', 'دخول')

@section('content')
    <div class="mx-auto max-w-md py-12">
        <div class="rounded-3xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-white">بوابة العميل</h1>
                <p class="mt-2 text-sm text-slate-400">ادخل بإيميلك وكلمة المرور لمتابعة مشروعك فقط</p>
            </div>

            <form method="POST" action="{{ route('portal.login.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="email">البريد الإلكتروني</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white placeholder-slate-500 outline-none ring-0 focus:border-emerald-400"
                        placeholder="name@example.com">
                </div>

                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="password">كلمة المرور</label>
                    <input id="password" name="password" type="password" required
                        class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white placeholder-slate-500 outline-none ring-0 focus:border-emerald-400"
                        placeholder="********">
                </div>

                <button type="submit" class="w-full rounded-xl bg-emerald-500 px-4 py-3 font-semibold text-slate-950 hover:bg-emerald-400">
                    دخول
                </button>
            </form>
        </div>
    </div>
@endsection
