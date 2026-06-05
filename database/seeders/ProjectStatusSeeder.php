<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProjectStatus;

class ProjectStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name_ar' => 'طلب جديد',
                'name_en' => 'New Request',
                'slug' => 'new-request',
                'color' => '#3498DB',
                'icon' => 'fa-star',
                'sort_order' => 1,
                'is_default' => true,
            ],
            [
                'name_ar' => 'قيد التحليل',
                'name_en' => 'Under Analysis',
                'slug' => 'under-analysis',
                'color' => '#9B59B6',
                'icon' => 'fa-search',
                'sort_order' => 2,
            ],
            [
                'name_ar' => 'بانتظار الاعتماد',
                'name_en' => 'Waiting for Approval',
                'slug' => 'waiting-approval',
                'color' => '#F39C12',
                'icon' => 'fa-clock',
                'sort_order' => 3,
            ],
            [
                'name_ar' => 'معتمد',
                'name_en' => 'Approved',
                'slug' => 'approved',
                'color' => '#1ABC9C',
                'icon' => 'fa-check-circle',
                'sort_order' => 4,
            ],
            [
                'name_ar' => 'قيد التنفيذ',
                'name_en' => 'In Progress',
                'slug' => 'in-progress',
                'color' => '#3498DB',
                'icon' => 'fa-spinner',
                'sort_order' => 5,
            ],
            [
                'name_ar' => 'مراجعة داخلية',
                'name_en' => 'Internal Review',
                'slug' => 'internal-review',
                'color' => '#E67E22',
                'icon' => 'fa-eye',
                'sort_order' => 6,
            ],
            [
                'name_ar' => 'مُرسل للعميل',
                'name_en' => 'Sent to Client',
                'slug' => 'sent-to-client',
                'color' => '#9B59B6',
                'icon' => 'fa-paper-plane',
                'sort_order' => 7,
            ],
            [
                'name_ar' => 'تعديلات',
                'name_en' => 'Revisions',
                'slug' => 'revisions',
                'color' => '#E74C3C',
                'icon' => 'fa-edit',
                'sort_order' => 8,
            ],
            [
                'name_ar' => 'مكتمل',
                'name_en' => 'Completed',
                'slug' => 'completed',
                'color' => '#27AE60',
                'icon' => 'fa-check-double',
                'sort_order' => 9,
            ],
            [
                'name_ar' => 'مغلق',
                'name_en' => 'Closed',
                'slug' => 'closed',
                'color' => '#95A5A6',
                'icon' => 'fa-lock',
                'sort_order' => 10,
            ],
            [
                'name_ar' => 'معلق',
                'name_en' => 'On Hold',
                'slug' => 'on-hold',
                'color' => '#F39C12',
                'icon' => 'fa-pause',
                'sort_order' => 11,
            ],
            [
                'name_ar' => 'ملغي',
                'name_en' => 'Cancelled',
                'slug' => 'cancelled',
                'color' => '#E74C3C',
                'icon' => 'fa-times-circle',
                'sort_order' => 12,
            ],
        ];

        foreach ($statuses as $status) {
            ProjectStatus::updateOrCreate(
                ['slug' => $status['slug']],
                $status
            );
        }

        $this->command->info('Project Statuses created successfully!');
    }
}
