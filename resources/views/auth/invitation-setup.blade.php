<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إعداد الحساب</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">{{ config('app.name') }}</h1>
            <p class="text-gray-500 mt-2">إعداد حسابك الجديد</p>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 p-4 bg-blue-50 rounded-xl">
            <p class="text-sm text-blue-800">
                <span class="font-medium">{{ $user->name }}</span><br>
                <span class="text-blue-600">{{ $user->email }}</span>
            </p>
        </div>

        <form action="{{ route('invitation.setup.post', $token) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">كلمة المرور</label>
                <input type="password" name="password" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">تأكيد كلمة المرور</label>
                <input type="password" name="password_confirmation" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-xl transition">
                إعداد الحساب
            </button>
        </form>
    </div>
</body>
</html>
