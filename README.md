# Air Menu | Workspace

<div align="center">
  <h3>🚀 نظام إدارة مشاريع وتشغيل احترافي متكامل</h3>
  <p>نظام شامل لإدارة المشاريع والمهام والعملاء والمالية لشركات الخدمات الرقمية</p>
</div>

---

## 📋 نظرة عامة

**Air Menu | Workspace** هو نظام إدارة مشاريع متكامل مصمم خصيصًا لشركات الخدمات الرقمية. يوفر النظام جميع الأدوات اللازمة لإدارة دورة حياة المشروع الكاملة من استقبال الطلب حتى التسليم والإغلاق.

### ✨ المميزات الرئيسية

- ✅ **إدارة العملاء (CRM)** - نظام شامل لإدارة العملاء والتواصل
- ✅ **إدارة المشاريع** - 12 حالة مختلفة + Workflow كامل
- ✅ **إدارة المهام** - Kanban Board + Calendar + Time Tracking
- ✅ **النظام المالي** - عروض أسعار + فواتير + عقود + مدفوعات
- ✅ **الصلاحيات** - 6 أدوار + 30+ صلاحية
- ✅ **متعدد اللغات** - دعم العربية والإنجليزية مع RTL/LTR
- ✅ **التقارير** - تقارير شاملة للمشاريع والمهام والمالية
- ✅ **Activity Log** - تتبع كامل لجميع الأنشطة
- ✅ **Notifications** - نظام إشعارات متقدم

---

## 🛠 التقنيات المستخدمة

### Backend
- **Laravel 10.x** - PHP Framework
- **MySQL 8.0+** - Database
- **Spatie Permission** - Roles & Permissions
- **Spatie Activity Log** - Activity Tracking

### Frontend
- **Tailwind CSS** - Utility-First CSS Framework
- **Alpine.js** - Lightweight JavaScript Framework
- **Font Awesome 6** - Icons
- **ApexCharts** - Charts & Graphs
- **Vite** - Build Tool

---

## 📦 متطلبات التشغيل

- PHP >= 8.1
- Composer
- Node.js >= 18.x
- npm or yarn
- MySQL >= 8.0
- Apache/Nginx

---

## 🚀 التنصيب والإعداد

### 1. استنساخ المشروع

```bash
git clone https://github.com/your-repo/air-menu-workspace.git
cd air-menu-workspace
```

### 2. تثبيت الحزم

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 3. إعداد البيئة

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. تكوين قاعدة البيانات

قم بتعديل ملف `.env` وأضف بيانات قاعدة البيانات:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=air_menu_workspace
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. إنشاء قاعدة البيانات وتشغيل Migrations

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE air_menu_workspace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed initial data (Roles, Permissions, Users, etc.)
php artisan db:seed
```

### 6. بناء الـ Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 7. تشغيل السيرفر

```bash
php artisan serve
```

الآن يمكنك الوصول للنظام عبر: `http://localhost:8000`

---

## 🔐 بيانات الدخول التجريبية

بعد تشغيل Seeders، ستتوفر البيانات التالية للدخول:

| الدور | البريد الإلكتروني | كلمة المرور |
|-------|-------------------|-------------|
| Super Admin | admin@airmenu.net | password |
| Admin | mohamed@airmenu.net | password |
| Project Manager | sara@airmenu.net | password |
| Designer | ahmed@airmenu.net | password |
| Developer | khaled@airmenu.net | password |
| Finance | fatima@airmenu.net | password |

---

## 📚 هيكل المشروع

```
air-menu-workspace/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/
│   ├── Policies/
│   └── Helpers/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── views/
│   ├── lang/
│   ├── css/
│   └── js/
├── routes/
│   └── web.php
├── public/
│   └── uploads/
└── config/
    └── workspace.php
```

---

## 🎯 الأدوار والصلاحيات

### Super Admin
- صلاحيات كاملة على النظام
- إدارة المستخدمين والصلاحيات
- إدارة الإعدادات

### Admin
- جميع الصلاحيات التشغيلية
- إدارة العملاء والمشاريع والمهام
- إدارة العروض والفواتير

### Project Manager
- إدارة المشاريع المسندة له
- إنشاء وتعيين المهام
- عرض التقارير

### Team Member
- عرض المشاريع المسندة
- إدارة المهام الخاصة به
- تتبع الوقت

### Finance
- إدارة العروض والفواتير
- تسجيل المدفوعات
- التقارير المالية

---

## 🌍 دعم متعدد اللغات

النظام يدعم اللغتين العربية والإنجليزية مع:
- RTL/LTR تلقائي
- خطوط مخصصة لكل لغة
- ترجمة كاملة للواجهات

لتغيير اللغة، استخدم القائمة في الهيدر أو أضف `?lang=ar` أو `?lang=en` في URL.

---

## 📊 قاعدة البيانات

النظام يحتوي على 26 جدول رئيسي:

### الجداول الأساسية
- users
- roles & permissions (Spatie)
- clients
- service_types
- project_statuses
- task_statuses

### جداول المشاريع
- projects
- project_user (pivot)
- project_attachments
- project_workflow_steps

### جداول المهام
- tasks
- task_comments
- task_attachments
- task_checklists
- time_logs

### الجداول المالية
- quotes & quote_items
- contracts
- invoices & invoice_items
- payments

### جداول أخرى
- change_requests
- notifications
- activity_log
- settings

---

## 🔧 التخصيص

### إعدادات النظام

يمكنك تخصيص إعدادات النظام عبر ملف `config/workspace.php`:

```php
'currency' => [
    'code' => 'SAR',
    'symbol' => 'ر.س',
],

'tax' => [
    'rate' => 15,
    'enabled' => true,
],

'project' => [
    'number_prefix' => 'PRJ',
],
```

### إضافة حالات مخصصة

يمكنك إضافة حالات جديدة للمشاريع والمهام عبر:
- الإعدادات -> حالات المشاريع
- الإعدادات -> حالات المهام

---

## 📈 التطوير المستقبلي

### المرحلة 1 (قريباً)
- [ ] Client Portal (بوابة العملاء)
- [ ] WhatsApp Integration
- [ ] Email Notifications
- [ ] Advanced Reports

### المرحلة 2
- [ ] Mobile App (iOS/Android)
- [ ] API للتكامل مع أنظمة خارجية
- [ ] AI-Powered Insights
- [ ] Resource Management

---

## 🐛 المشاكل والدعم

إذا واجهتك أي مشاكل:
1. تحقق من ملف `.env`
2. تأكد من تشغيل `composer install` و `npm install`
3. راجع logs في `storage/logs/laravel.log`

---

## 📝 الترخيص

هذا المشروع ملك لـ **Digital Business Solutions** ومحمي بحقوق الملكية.

---

## 👥 الفريق

تم تطوير النظام بواسطة فريق Digital Business Solutions

---

<div align="center">
  <p>صُنع بـ ❤️ في السعودية</p>
  <p>&copy; 2024 Digital Business Solutions. All rights reserved.</p>
</div>
