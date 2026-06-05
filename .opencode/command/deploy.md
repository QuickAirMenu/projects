---
description: نشر التطبيق على projects.airmenu.net (يستدعي تسلسل السلحفاة ثم النسر)
agent: eagle
---

نفّذ النشر الكامل لتطبيق Air Menu Projects على الإنتاج باتباع الخطوات:

1. **مراجعة أمان أولاً** (دور السلحفاة): تأكد أن `.env` غير مُتتبع في git، `APP_DEBUG=false`، لا أسرار مكشوفة.
2. `git pull origin main` محلياً.
3. إن تغيّرت الأصول: `npm run build`.
4. rsync إلى `~/domains/airmenu.net/projects_app` (استبعاد `.git`, `node_modules`, `vendor`).
5. على السيرفر: `~/bin/composer install --no-dev --optimize-autoloader`.
6. `php artisan migrate --force` (إن وُجدت هجرات جديدة).
7. `php artisan config:cache && route:cache && view:cache`.
8. تحقق: `curl -I https://projects.airmenu.net/` يجب أن يرجع 200/302.

بيانات الاتصال: `ssh -p 65002 u227212075@185.214.124.218`
