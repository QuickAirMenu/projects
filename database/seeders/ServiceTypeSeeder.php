<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceTypes = [
            [
                'name_ar' => 'تصميم الهوية والبروفايل',
                'name_en' => 'Brand Identity & Profile Design',
                'description_ar' => 'تصميم شعار، بطاقات أعمال، ملف تعريف الشركة',
                'description_en' => 'Logo design, business cards, company profile',
                'icon' => 'fa-palette',
                'color' => '#E74C3C',
                'sort_order' => 1,
            ],
            [
                'name_ar' => 'تصميم المواقع والمتاجر',
                'name_en' => 'Website & E-commerce Design',
                'description_ar' => 'تصميم وتطوير المواقع الإلكترونية والمتاجر',
                'description_en' => 'Website and e-commerce design and development',
                'icon' => 'fa-globe',
                'color' => '#3498DB',
                'sort_order' => 2,
            ],
            [
                'name_ar' => 'إدارة السوشيال ميديا',
                'name_en' => 'Social Media Management',
                'description_ar' => 'إدارة حسابات التواصل الاجتماعي ونشر المحتوى',
                'description_en' => 'Social media account management and content publishing',
                'icon' => 'fa-share-nodes',
                'color' => '#9B59B6',
                'sort_order' => 3,
            ],
            [
                'name_ar' => 'الحملات التسويقية',
                'name_en' => 'Marketing Campaigns',
                'description_ar' => 'حملات إعلانية ممولة وتسويق رقمي',
                'description_en' => 'Paid advertising campaigns and digital marketing',
                'icon' => 'fa-bullhorn',
                'color' => '#F39C12',
                'sort_order' => 4,
            ],
            [
                'name_ar' => 'التصميم الجرافيكي',
                'name_en' => 'Graphic Design',
                'description_ar' => 'تصميم منشورات، بنرات، إعلانات',
                'description_en' => 'Posts, banners, and advertisement design',
                'icon' => 'fa-image',
                'color' => '#1ABC9C',
                'sort_order' => 5,
            ],
            [
                'name_ar' => 'تطوير الأنظمة',
                'name_en' => 'System Development',
                'description_ar' => 'تطوير أنظمة ويب وتطبيقات مخصصة',
                'description_en' => 'Web systems and custom application development',
                'icon' => 'fa-code',
                'color' => '#2C3E50',
                'sort_order' => 6,
            ],
            [
                'name_ar' => 'خدمات تقنية متنوعة',
                'name_en' => 'Various Technical Services',
                'description_ar' => 'استشارات تقنية وخدمات تشغيلية أخرى',
                'description_en' => 'Technical consulting and other operational services',
                'icon' => 'fa-wrench',
                'color' => '#95A5A6',
                'sort_order' => 7,
            ],
            [
                'name_ar' => 'التصوير والمونتاج',
                'name_en' => 'Photography & Video Editing',
                'description_ar' => 'تصوير فوتوغرافي ومونتاج فيديو',
                'description_en' => 'Photography and video editing services',
                'icon' => 'fa-camera',
                'color' => '#E67E22',
                'sort_order' => 8,
            ],
            [
                'name_ar' => 'كتابة المحتوى',
                'name_en' => 'Content Writing',
                'description_ar' => 'كتابة محتوى تسويقي وإبداعي',
                'description_en' => 'Marketing and creative content writing',
                'icon' => 'fa-pen',
                'color' => '#27AE60',
                'sort_order' => 9,
            ],
            [
                'name_ar' => 'استشارات تسويقية',
                'name_en' => 'Marketing Consultancy',
                'description_ar' => 'استشارات في استراتيجيات التسويق',
                'description_en' => 'Marketing strategy consultancy',
                'icon' => 'fa-chart-line',
                'color' => '#8E44AD',
                'sort_order' => 10,
            ],
        ];

        foreach ($serviceTypes as $serviceType) {
            ServiceType::updateOrCreate(
                ['name_en' => $serviceType['name_en']],
                $serviceType
            );
        }

        $this->command->info('Service Types created successfully!');
    }
}
