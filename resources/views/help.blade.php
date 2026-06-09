@extends('layouts.app')
@section('title', 'مركز المساعدة')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">مركز المساعدة</h1>
    <p class="text-gray-500 mt-1">دليل استخدام شامل لمنصة {{ config('app.name') }}</p>
</div>

<!-- Search -->
<div class="mb-8 max-w-2xl">
    <div class="relative">
        <i class="fas fa-search absolute {{ is_rtl() ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input type="text" id="helpSearch" placeholder="ابحث في الدليل..."
               class="w-full {{ is_rtl() ? 'pr-10' : 'pl-10' }} border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
</div>

<!-- Help Sections Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5" id="helpSections">
    @php $sections = [
        ['id' => 'getting-started', 'icon' => 'fa-rocket', 'color' => 'blue', 'title' => 'البدء السريع',
         'items' => [
             ['q' => 'كيف أبدأ استخدام المنصة؟', 'a' => 'ابدأ بإنشاء عميل من قسم "العملاء"، ثم أنشئ مشروعاً من قسم "المشاريع"، وادعُ فريقك من قسم "المستخدمون".'],
             ['q' => 'كيف أدعو فريقي؟', 'a' => 'اذهب إلى "المستخدمون" → "إضافة مستخدم"، أدخل اسم وبريد العضو الجديد، وسيتم إرسال دعوة انضمام عبر البريد الإلكتروني.'],
             ['q' => 'ما هي الصلاحيات المتاحة؟', 'a' => 'توجد صلاحيات متعددة: super-admin (كل الصلاحيات)، admin (إدارة)، team-member (تنفيذ المهام). يمكن تخصيص صلاحيات الأدوار من قسم "الإعدادات".'],
         ]],
        ['id' => 'clients', 'icon' => 'fa-users', 'color' => 'green', 'title' => 'العملاء',
         'items' => [
             ['q' => 'كيف أضيف عميلاً جديداً؟', 'a' => 'من قائمة "العملاء"، اضغط "إضافة عميل"، أدخل البيانات المطلوبة (الاسم، الإيميل، الهاتف)، ثم احفظ.'],
             ['q' => 'كيف أرسل دعوة بوابة العميل؟', 'a' => 'من صفحة تفاصيل العميل، اضغط "إرسال دعوة البوابة". سيصله إيميل لإنشاء كلمة مرور والدخول لبوابته الخاصة.'],
             ['q' => 'ماذا يرى العميل في بوابته؟', 'a' => 'يرى العميل مشاريعه، الفواتير، حالة المشروع، ويمكنه التواصل مع الفريق من خلال البوابة.'],
         ]],
        ['id' => 'projects', 'icon' => 'fa-briefcase', 'color' => 'purple', 'title' => 'المشاريع',
         'items' => [
             ['q' => 'كيف أنشئ مشروعاً؟', 'a' => 'من قسم "المشاريع"، اضغط "مشروع جديد"، اختر العميل ونوع الخدمة والمدير والميزانية، ثم احفظ.'],
             ['q' => 'ما هو سير العمل (Workflow)؟', 'a' => 'كل مشروع له سير عمل مخصص حسب نوع الخدمة. يمكنك إكمال الخطوات أو التراجع عنها من صفحة المشروع.'],
             ['q' => 'كيف أسلم المشروع للعميل؟', 'a' => 'من صفحة المشروع، اضغط "تسليم المشروع"، سيتم إرسال إشعار للعميل بأن المشروع جاهز للتسليم.'],
         ]],
        ['id' => 'tasks', 'icon' => 'fa-tasks', 'color' => 'amber', 'title' => 'المهام',
         'items' => [
             ['q' => 'كيف أدير المهام؟', 'a' => 'استخدم لوحة كانبان لسحب وإفلات المهام بين الحالات المختلفة. يمكنك أيضاً عرض المهام كقائمة أو تقويم.'],
             ['q' => 'كيف أسجل وقت العمل؟', 'a' => 'من صفحة المهمة، أضف تسجيل وقت (Time Log) بالساعات والتاريخ والوصف.'],
             ['q' => 'ما هي قوائم المراجعة (Checklists)؟', 'a' => 'داخل كل مهمة، يمكنك إضافة قائمة مراجعة بخطوات فرعية لتتبع التقدم بدقة.'],
         ]],
        ['id' => 'finance', 'icon' => 'fa-file-invoice', 'color' => 'emerald', 'title' => 'النظام المالي',
         'items' => [
             ['q' => 'كيف أنشئ عرض سعر؟', 'a' => 'من "عروض الأسعار" → "إنشاء عرض سعر"، اختر العميل ونوع الخدمة، أضف البنود، ثم احفظ. يمكنك إرساله للعميل عبر الإيميل أو WhatsApp.'],
             ['q' => 'كيف أحول عرض سعر إلى فاتورة؟', 'a' => 'بعد اعتماد العرض، اضغط "تحويل إلى فاتورة" من صفحة العرض. سيتم إنشاء فاتورة بالبيانات تلقائياً.'],
             ['q' => 'كيف أسجل دفعة؟', 'a' => 'من صفحة الفاتورة، اضغط "تسجيل دفعة"، أدخل المبلغ وطريقة الدفع والتاريخ، ثم احفظ.'],
             ['q' => 'كيف أحمّل PDF؟', 'a' => 'كل من عروض الأسعار والفواتير والعقود تدعم تحميل نسخة PDF من صفحة التفاصيل أو من الرابط العام.'],
             ['q' => 'ما هو الأرشيف؟', 'a' => 'عند حذف أي عنصر مالي، ينتقل إلى "الأرشيف" ويمكن استعادته لاحقاً. زر "الأرشيف" موجود في كل صفحة.'],
         ]],
        ['id' => 'reports', 'icon' => 'fa-chart-bar', 'color' => 'rose', 'title' => 'التقارير',
         'items' => [
             ['q' => 'ما التقارير المتاحة؟', 'a' => 'توجد 4 أنواع: تقارير المشاريع، تقارير المهام، التقارير المالية، وتقارير الأداء.'],
             ['q' => 'كيف أطلع على الأداء المالي؟', 'a' => 'من "التقارير" → "المالية"، يمكنك رؤية الإيرادات، الأرباح، الفواتير غير المدفوعة، والمدفوعات الشهرية.'],
         ]],
    ]; @endphp

    @foreach($sections as $section)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 help-section" data-section="{{ $section['id'] }}">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-{{ $section['color'] }}-100 flex items-center justify-center">
                <i class="fas {{ $section['icon'] }} text-{{ $section['color'] }}-600"></i>
            </div>
            <h2 class="font-bold text-lg text-gray-800">{{ $section['title'] }}</h2>
        </div>
        <div class="space-y-3">
            @foreach($section['items'] as $item)
            <details class="group border border-gray-100 rounded-xl">
                <summary class="px-4 py-3 cursor-pointer text-sm font-medium text-gray-700 hover:text-blue-600 list-none flex items-center justify-between">
                    {{ $item['q'] }}
                    <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="px-4 pb-4 text-sm text-gray-500 leading-relaxed">{{ $item['a'] }}</div>
            </details>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script>
document.getElementById('helpSearch').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.help-section').forEach(section => {
        const text = section.textContent.toLowerCase();
        section.style.display = text.includes(query) ? '' : 'none';
    });
});
</script>
@endpush
@endsection
