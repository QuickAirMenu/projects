# Air Menu | Projects

نظام Laravel لإدارة مشاريع Air Menu على `https://projects.airmenu.net`.

## نظرة عامة

Air Menu Projects منصة عملية لإدارة المشاريع والعملاء والمالية لشركات الخدمات. النظام يدعم العربية والإنجليزية، والصلاحيات، وتتبع النشاط، وتدفق العمل من العرض إلى الفاتورة والتحصيل.

## المكونات المتاحة

- المشاريع والعملاء والمهام وتتبع الوقت والتقارير.
- عروض الأسعار مع KPIs، ورابط عرض عام، وتحويل العرض إلى فاتورة.
- عرض الأسعار يدعم عناصر مسبقة التعبئة عند إنشاء الفاتورة من العرض.
- العقود مع بيانات تجريبية وعلاقتها بالمشاريع والفواتير.
- الفواتير والمدفوعات ومرفقات إثبات السداد.
- PDF للعروض والعقود والفواتير مع دعم شعار الشركة.
- بوابة العملاء مع دعوة الإعداد، ودخول البوابة، ومشاركة WhatsApp، وإرسال البريد عبر SMTP.
- الإعدادات العامة تشمل بيانات الشركة والبريد والشعار والعملة والضريبة.

## المتطلبات

- PHP 8.1+
- Composer
- Node.js 18+ و npm
- MySQL 8+

## إعداد سريع

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan storage:link
```

اضبط في `.env` على الأقل:

```env
APP_URL=https://projects.airmenu.net
DB_DATABASE=air_menu_projects
MAIL_MAILER=smtp
```

## النشر

- في الإنتاج، تُبنى الأصول محليًا ثم تُنشر على الخادم.
- لا حاجة لـ Node.js على الخادم حسب إعداد المشروع الحالي.
- بعد النشر شغّل أو حدّث cache/storage حسب بيئة الاستضافة.

## بيانات تجريبية

بعد `--seed` تتوفر حسابات تجريبية، منها:

| الدور | البريد الإلكتروني | كلمة المرور |
|---|---|---|
| Super Admin | admin@airmenu.net | password |
| Admin | mohamed@airmenu.net | password |
| Project Manager | sara@airmenu.net | password |
| Designer | ahmed@airmenu.net | password |
| Developer | dev@workspace.com | password |
| Finance | fatima@airmenu.net | password |

## ملاحظات

- إعدادات البريد موجودة داخل الإعدادات العامة وتُستخدم مع دعوات البوابة وإرسال العروض.
- الشعار يستخدم داخل ملفات PDF إذا كان معرفًا في الإعدادات.
