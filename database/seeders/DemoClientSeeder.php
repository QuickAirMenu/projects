<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoClientSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();

        $clients = [
            [
                'name' => 'محمد الأحمدي',
                'company_name' => 'مجموعة الأحمدي التجارية',
                'email' => 'mohammed@ahmadi-group.com',
                'phone' => '+966501234567',
                'whatsapp' => '+966501234567',
                'city' => 'الرياض',
                'country' => 'Saudi Arabia',
                'status' => 'vip',
                'industry' => 'تجارة',
                'source' => 'referral',
                'lifetime_value' => 85000,
                'notes' => 'عميل مميز - يفضل التواصل عبر الواتساب',
            ],
            [
                'name' => 'سارة العمري',
                'company_name' => 'مطاعم سارة',
                'email' => 'sara@sara-restaurants.com',
                'phone' => '+966551234567',
                'city' => 'جدة',
                'country' => 'Saudi Arabia',
                'status' => 'active',
                'industry' => 'مطاعم وضيافة',
                'source' => 'website',
                'lifetime_value' => 42000,
            ],
            [
                'name' => 'خالد المطيري',
                'company_name' => 'شركة المطيري للمقاولات',
                'email' => 'khalid@mutairi-contracting.com',
                'phone' => '+966561234567',
                'city' => 'الدمام',
                'country' => 'Saudi Arabia',
                'status' => 'active',
                'industry' => 'مقاولات',
                'source' => 'social_media',
                'lifetime_value' => 28000,
            ],
            [
                'name' => 'نورة الغامدي',
                'company_name' => 'ستوديو نورة للتصميم',
                'email' => 'noura@noura-studio.com',
                'phone' => '+966571234567',
                'city' => 'الرياض',
                'country' => 'Saudi Arabia',
                'status' => 'active',
                'industry' => 'تصميم وإبداع',
                'source' => 'referral',
                'lifetime_value' => 15000,
            ],
            [
                'name' => 'عبدالله الزهراني',
                'company_name' => 'مدرسة الزهراني الخاصة',
                'email' => 'abdullah@zahrani-school.edu.sa',
                'phone' => '+966581234567',
                'city' => 'مكة المكرمة',
                'country' => 'Saudi Arabia',
                'status' => 'potential',
                'industry' => 'تعليم',
                'source' => 'exhibition',
                'lifetime_value' => 0,
                'notes' => 'في مرحلة التفاوض - يبحث عن موقع وأنظمة إدارة',
            ],
            [
                'name' => 'فاطمة الشمري',
                'company_name' => 'عيادات الشمري الطبية',
                'email' => 'fatima@shamri-clinics.com',
                'phone' => '+966591234567',
                'city' => 'الرياض',
                'country' => 'Saudi Arabia',
                'status' => 'inactive',
                'industry' => 'رعاية صحية',
                'source' => 'cold_call',
                'lifetime_value' => 5500,
            ],
        ];

        foreach ($clients as $data) {
            $data['assigned_to'] = $admin?->id;
            $data['created_by'] = $admin?->id ?? 1;
            Client::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
    }
}
