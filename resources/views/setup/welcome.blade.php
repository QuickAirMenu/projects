<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iProjects - إعداد النظام</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Cairo',sans-serif}</style>
</head>
<body class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-8 text-center">
            <div class="text-5xl mb-3">🚀</div>
            <h1 class="text-3xl font-bold">مرحباً بك في iProjects</h1>
            <p class="text-indigo-100 mt-2">نظام إدارة المشاريع والمالية</p>
            <p class="text-indigo-200 text-sm mt-1">إعداد النظام لأول مرة — يستغرق دقيقة واحدة</p>
        </div>

        <form action="{{ url('/setup/' . $secret) }}" method="POST" class="p-8 space-y-6">
            @csrf
            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-7 h-7 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-sm font-bold">1</span>
                    بيانات الشركة
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">اسم الشركة *</label>
                        <input type="text" name="company_name" required value="{{ old('company_name') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="مثال: شركة TopAccounts">
                        @error('company_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">العملة *</label>
                        <select name="currency" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
                            <option value="SAR">ريال سعودي (SAR)</option>
                            <option value="AED">درهم إماراتي (AED)</option>
                            <option value="USD">دولار أمريكي (USD)</option>
                            <option value="EUR">يورو (EUR)</option>
                            <option value="EGP">جنيه مصري (EGP)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">اللغة *</label>
                        <select name="language" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
                            <option value="ar">العربية</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">المنطقة الزمنية *</label>
                        <select name="timezone" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
                            <option value="Asia/Riyadh">الرياض (GMT+3)</option>
                            <option value="Asia/Dubai">دبي (GMT+4)</option>
                            <option value="Africa/Cairo">القاهرة (GMT+2)</option>
                            <option value="Asia/Kuwait">الكويت (GMT+3)</option>
                            <option value="Asia/Qatar">الدوحة (GMT+3)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">نسبة الضريبة (%) *</label>
                        <input type="number" name="tax_rate" required value="15" step="0.01" min="0" max="100"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>
            <hr class="border-gray-200">
            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-7 h-7 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-sm font-bold">2</span>
                    حساب المدير
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">الاسم الكامل *</label>
                        <input type="text" name="admin_name" required value="{{ old('admin_name') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500"
                            placeholder="الاسم الكامل للمدير">
                        @error('admin_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني *</label>
                        <input type="email" name="admin_email" required value="{{ old('admin_email') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500"
                            placeholder="admin@company.com">
                        @error('admin_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">كلمة المرور *</label>
                        <input type="password" name="admin_password" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500"
                            placeholder="8 أحرف على الأقل" minlength="8">
                        @error('admin_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">تأكيد كلمة المرور *</label>
                        <input type="password" name="admin_password_confirmation" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500"
                            placeholder="أعد كتابة كلمة المرور" minlength="8">
                    </div>
                </div>
            </div>
            <div class="pt-2">
                <button type="submit"
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-3.5 rounded-xl text-lg transition shadow-lg">
                    إعداد النظام الآن 🚀
                </button>
                <p class="text-center text-gray-400 text-xs mt-3">سيتم إنشاء حساب المدير + الإعدادات الأولية + الأرقام التسلسلية</p>
            </div>
        </form>
    </div>
</body>
</html>
