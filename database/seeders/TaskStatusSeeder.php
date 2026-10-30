<?php

namespace Database\Seeders;

use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name_ar' => 'للعمل عليها',
                'name_en' => 'To Do',
                'slug' => 'to-do',
                'color' => '#95A5A6',
                'icon' => 'fa-list',
                'sort_order' => 1,
                'is_default' => true,
            ],
            [
                'name_ar' => 'قيد التنفيذ',
                'name_en' => 'In Progress',
                'slug' => 'in-progress',
                'color' => '#3498DB',
                'icon' => 'fa-spinner',
                'sort_order' => 2,
            ],
            [
                'name_ar' => 'قيد المراجعة',
                'name_en' => 'In Review',
                'slug' => 'in-review',
                'color' => '#F39C12',
                'icon' => 'fa-eye',
                'sort_order' => 3,
            ],
            [
                'name_ar' => 'منجزة',
                'name_en' => 'Done',
                'slug' => 'done',
                'color' => '#27AE60',
                'icon' => 'fa-check-circle',
                'sort_order' => 4,
            ],
            [
                'name_ar' => 'محجوبة',
                'name_en' => 'Blocked',
                'slug' => 'blocked',
                'color' => '#E74C3C',
                'icon' => 'fa-ban',
                'sort_order' => 5,
            ],
            [
                'name_ar' => 'متأخرة',
                'name_en' => 'Overdue',
                'slug' => 'overdue',
                'color' => '#C0392B',
                'icon' => 'fa-exclamation-triangle',
                'sort_order' => 6,
            ],
        ];

        foreach ($statuses as $status) {
            TaskStatus::updateOrCreate(
                ['slug' => $status['slug']],
                $status
            );
        }

        $this->command->info('Task Statuses created successfully!');
    }
}
