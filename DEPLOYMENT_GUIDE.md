# 🚀 دليل النشر والتفعيل - Air Menu | Workspace

## الحالة الحالية

النظام جاهز للنشر ويشمل:

- Quotes مع KPI dashboard ومسار `quote -> invoice`
- Contracts مع ارتباط واضح بـ invoices
- PDFs مع دعم الشعار
- Public quote link مع دعوة عبر WhatsApp أو email
- Client portal مع invite flow للعميل
- Clients, projects, tasks, comments, attachments, notifications, activity log
- Roles & permissions، settings، localization، RTL/LTR

## التفعيل السريع

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

عدّل إعدادات قاعدة البيانات في `.env` قبل التشغيل.

## بيانات الدخول

```text
Super Admin: admin@airmenu.net / password
Admin: mohamed@airmenu.net / password
Project Manager: sara@airmenu.net / password
```

## ملاحظات تشغيل

- تأكد من إعداد `APP_URL` وملف الشعار إذا كنت ستصدر PDF بالشعار.
- روابط العروض العامة ودعوات العملاء تحتاج إرسال البريد/WhatsApp المفعّل في بيئة التشغيل.
- راجع `README.md` فقط إذا احتجت تفاصيل تثبيت أو بيئة إضافية.
