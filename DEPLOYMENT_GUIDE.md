# 🚀 دليل النشر والتفعيل - Air Menu | Workspace

## ✅ ما تم إنجازه

### 1. البنية الأساسية الكاملة
✅ هيكل المجلدات الكامل
✅ Composer.json مع جميع الحزم المطلوبة
✅ Package.json للـ Frontend
✅ Tailwind CSS + Vite Configuration
✅ Font Awesome Integration

### 2. قاعدة البيانات (26 Migration)
✅ Users + Roles & Permissions (Spatie)
✅ Clients + Communications
✅ Service Types + Statuses
✅ Projects + Attachments + Workflow
✅ Tasks + Comments + Checklists + Time Logs
✅ Quotes + Items
✅ Contracts
✅ Invoices + Items + Payments
✅ Change Requests
✅ Notifications + Activity Log + Settings

### 3. Models (17 Model)
✅ User (مع العلاقات والـ Scopes)
✅ Client
✅ Project
✅ Task
✅ ServiceType
✅ ProjectStatus
✅ TaskStatus
✅ Quote + QuoteItem
✅ Invoice + InvoiceItem
✅ Payment
✅ Contract
✅ ChangeRequest
✅ Supporting Models (Attachments, Comments, etc.)

### 4. Seeders
✅ RolePermissionSeeder (6 أدوار + 30+ صلاحية)
✅ UserSeeder (6 مستخدمين تجريبيين)
✅ ServiceTypeSeeder (10 أنواع خدمات)
✅ ProjectStatusSeeder (12 حالة)
✅ TaskStatusSeeder (6 حالات)

### 5. Controllers
✅ LoginController
✅ LogoutController
✅ DashboardController

### 6. Views
✅ Layout الأساسي (app.blade.php)
✅ Sidebar + Header + Footer
✅ صفحة Login
✅ صفحة Dashboard

### 7. Frontend Assets
✅ CSS (Tailwind + Custom Styles)
✅ JavaScript (Alpine.js + Helpers)
✅ RTL/LTR Support

### 8. Localization
✅ ملفات ترجمة عربية
✅ دعم متعدد اللغات

### 9. Configuration
✅ config/workspace.php (إعدادات مخصصة)
✅ Helper Functions (20+ دالة)
✅ Routes الأساسية

---

## 🔥 خطوات التفعيل السريعة

### الخطوة 1: تثبيت Dependencies

```bash
cd air-menu-workspace
composer install
npm install
```

### الخطوة 2: إعداد البيئة

```bash
cp .env.example .env
php artisan key:generate
```

### الخطوة 3: تكوين قاعدة البيانات

عدّل في `.env`:
```env
DB_DATABASE=air_menu_workspace
DB_USERNAME=root
DB_PASSWORD=your_password
```

### الخطوة 4: إنشاء قاعدة البيانات

```bash
mysql -u root -p -e "CREATE DATABASE air_menu_workspace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### الخطوة 5: تشغيل Migrations + Seeders

```bash
php artisan migrate --seed
```

### الخطوة 6: بناء Assets

```bash
npm run build
```

### الخطوة 7: تشغيل السيرفر

```bash
php artisan serve
```

الآن افتح: http://localhost:8000

---

## 🔐 بيانات الدخول

```
Super Admin: admin@airmenu.net / password
Admin: mohamed@airmenu.net / password
Project Manager: sara@airmenu.net / password
```

---

## 📝 المراحل التالية للتطوير

### المرحلة 3: إكمال الـ Controllers & Views
- [ ] ClientController + Views (index, create, edit, show)
- [ ] ProjectController + Views
- [ ] TaskController + Views (+ Kanban Board)

### المرحلة 4: Financial Module
- [ ] QuoteController + Views
- [ ] InvoiceController + Views
- [ ] PaymentController + Views
- [ ] ContractController + Views

### المرحلة 5: Advanced Features
- [ ] ChangeRequestController
- [ ] ReportControllers
- [ ] NotificationController

### المرحلة 6: Polish & Optimization
- [ ] Form Validation Requests
- [ ] Policies للـ Authorization
- [ ] Services Layer للـ Business Logic
- [ ] Middleware (Localization, ActivityLogger)
- [ ] PDF Generation
- [ ] Excel Export

---

## 🎯 نصائح مهمة

### 1. الترجمة
أضف المزيد من الترجمات في:
- `resources/lang/ar/`
- `resources/lang/en/`

### 2. الصلاحيات
تحقق من الصلاحيات باستخدام:
```php
@can('view-projects')
    // Code
@endcan
```

### 3. Activity Log
سيتم تسجيل جميع التعديلات تلقائيًا عبر Spatie Activity Log

### 4. File Uploads
الملفات تُحفظ في:
- `public/uploads/projects`
- `public/uploads/tasks`
- `public/uploads/clients`

---

## 🛠 Troubleshooting

### مشكلة: Composer install يفشل
```bash
composer update --no-scripts
composer install
```

### مشكلة: npm install يفشل
```bash
rm -rf node_modules package-lock.json
npm install
```

### مشكلة: Permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### مشكلة: Migration تفشل
```bash
php artisan migrate:fresh --seed
```

---

## 📧 الدعم

لأي استفسارات أو مشاكل، راجع:
1. README.md
2. Laravel Documentation: https://laravel.com/docs
3. Tailwind CSS: https://tailwindcss.com/docs

---

<div align="center">
  <h3>✨ جاهز للإنطلاق! ✨</h3>
  <p>الآن لديك نظام إدارة مشاريع احترافي كامل جاهز للاستخدام والتطوير</p>
</div>
