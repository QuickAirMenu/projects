<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - {{ __('messages.welcome') }}</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-white antialiased">
    <!-- Hero -->
    <div class="min-h-screen flex flex-col items-center justify-center px-4 text-center">
        <div class="mb-8 flex h-20 w-20 items-center justify-center rounded-2xl bg-blue-600 text-4xl font-bold">AM</div>
        <h1 class="text-4xl md:text-6xl font-extrabold mb-4">{{ config('app.name') }}</h1>
        <p class="text-lg md:text-xl text-slate-400 max-w-2xl mb-10">
            منصة متكاملة لإدارة المشاريع، المهام، العملاء، العقود، الفواتير، وعروض الأسعار — كل شيء في مكان واحد.
        </p>
        <div class="flex flex-wrap gap-4 justify-center mb-16">
            <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3.5 rounded-xl font-semibold text-lg transition">
                <i class="fas fa-sign-in-alt me-2"></i>تسجيل الدخول
            </a>
            <a href="#features" class="border border-white/20 hover:bg-white/10 text-white px-8 py-3.5 rounded-xl font-semibold text-lg transition">
                <i class="fas fa-arrow-down me-2"></i>استكشف المميزات
            </a>
        </div>

        <!-- Features Grid -->
        <div id="features" class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl w-full">
            @php $features = [
                ['icon' => 'fa-briefcase', 'title' => 'إدارة المشاريع', 'desc' => 'تتبع مراحل المشروع من البداية حتى التسليم مع سير عمل احترافي'],
                ['icon' => 'fa-tasks', 'title' => 'إدارة المهام', 'desc' => 'لوحة كانبان، قوائم مراجعة، تتبع الوقت، وتقارير الأداء'],
                ['icon' => 'fa-file-invoice', 'title' => 'النظام المالي', 'desc' => 'عروض أسعار، فواتير، عقود، مدفوعات — بتنسيق PDF احترافي'],
                ['icon' => 'fa-users', 'title' => 'بوابة العميل', 'desc' => 'واجهة خاصة للعملاء لمتابعة مشاريعهم وفواتيرهم'],
                ['icon' => 'fa-chart-bar', 'title' => 'تقارير شاملة', 'desc' => 'تقارير مالية، أداء، مشاريع، ومهام — كل البيانات في متناول يدك'],
                ['icon' => 'fa-shield-halved', 'title' => 'أمان وصلاحيات', 'desc' => 'نظام صلاحيات متكامل يضمن وصول كل مستخدم لما يحتاجه فقط'],
            ]; @endphp
            @foreach($features as $f)
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 text-start hover:bg-white/10 transition">
                <div class="w-11 h-11 rounded-xl bg-blue-600/20 flex items-center justify-center mb-4">
                    <i class="fas {{ $f['icon'] }} text-blue-400 text-xl"></i>
                </div>
                <h3 class="font-bold text-lg mb-2">{{ $f['title'] }}</h3>
                <p class="text-slate-400 text-sm leading-relaxed">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>

        <!-- Footer -->
        <div class="mt-20 py-8 border-t border-white/10 w-full max-w-5xl text-slate-500 text-sm">
            <div class="flex flex-wrap justify-between gap-4">
                <span>&copy; {{ date('Y') }} {{ config('app.name') }}. جميع الحقوق محفوظة.</span>
                <div class="flex gap-6">
                    <a href="{{ route('login') }}" class="hover:text-white">تسجيل الدخول</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
