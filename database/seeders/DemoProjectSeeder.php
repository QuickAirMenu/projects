<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoProjectSeeder extends Seeder
{
    public function run(): void
    {
        $manager  = User::whereHas('roles', fn($q) => $q->where('name', 'project-manager'))->first();
        $member   = User::whereHas('roles', fn($q) => $q->where('name', 'team-member'))->first();
        $clients  = Client::all();
        $statuses = ProjectStatus::all()->keyBy('slug');
        $services = ServiceType::all();

        if ($clients->isEmpty() || $statuses->isEmpty() || $services->isEmpty()) {
            return;
        }

        $projects = [
            [
                'name'        => 'تصميم هوية بصرية - مجموعة الأحمدي',
                'description' => 'تصميم هوية بصرية متكاملة تشمل الشعار والألوان والخطوط وتطبيقاتها على المواد التسويقية',
                'status_slug' => 'in-progress',
                'service_idx' => 0,
                'priority'    => 'high',
                'budget'      => 15000,
                'start_date'  => now()->subDays(20)->toDateString(),
                'deadline'    => now()->addDays(10)->toDateString(),
                'progress'    => 65,
                'client_idx'  => 0,
            ],
            [
                'name'        => 'تطوير موقع إلكتروني - مطاعم سارة',
                'description' => 'تصميم وتطوير موقع إلكتروني احترافي مع نظام حجز طاولات ومنيو رقمي',
                'status_slug' => 'sent-to-client',
                'service_idx' => 1,
                'priority'    => 'high',
                'budget'      => 25000,
                'start_date'  => now()->subDays(45)->toDateString(),
                'deadline'    => now()->addDays(5)->toDateString(),
                'progress'    => 90,
                'client_idx'  => 1,
            ],
            [
                'name'        => 'إدارة منصات التواصل الاجتماعي - المطيري',
                'description' => 'إدارة حسابات انستغرام وتويتر ولينكدإن مع إنشاء محتوى شهري',
                'status_slug' => 'in-progress',
                'service_idx' => 2,
                'priority'    => 'medium',
                'budget'      => 8000,
                'start_date'  => now()->subDays(15)->toDateString(),
                'deadline'    => now()->addDays(15)->toDateString(),
                'progress'    => 40,
                'client_idx'  => 2,
            ],
            [
                'name'        => 'حملة تسويقية رمضان - ستوديو نورة',
                'description' => 'تصميم وتنفيذ حملة تسويقية رقمية متكاملة لموسم رمضان',
                'status_slug' => 'completed',
                'service_idx' => 3,
                'priority'    => 'urgent',
                'budget'      => 12000,
                'start_date'  => now()->subDays(60)->toDateString(),
                'deadline'    => now()->subDays(5)->toDateString(),
                'progress'    => 100,
                'client_idx'  => 3,
            ],
            [
                'name'        => 'تصميم مواد تسويقية - الغامدي',
                'description' => 'تصميم بروشور وفولدر ورولاب وبنرات للمدرسة',
                'status_slug' => 'under-analysis',
                'service_idx' => 4,
                'priority'    => 'low',
                'budget'      => 5000,
                'start_date'  => now()->addDays(5)->toDateString(),
                'deadline'    => now()->addDays(30)->toDateString(),
                'progress'    => 0,
                'client_idx'  => 4,
            ],
        ];

        $taskStatusTodo = TaskStatus::where('slug', 'to-do')->first();
        $taskStatusDone = TaskStatus::where('slug', 'done')->first();
        $taskStatusIP   = TaskStatus::where('slug', 'in-progress')->first();

        foreach ($projects as $pData) {
            $client     = $clients[$pData['client_idx']] ?? $clients->first();
            $statusId   = $statuses[$pData['status_slug']]?->id ?? $statuses->first()->id;
            $serviceId  = $services[$pData['service_idx']]?->id ?? $services->first()->id;

            $project = Project::updateOrCreate([
                'name' => $pData['name'],
            ], [
                'name'                => $pData['name'],
                'client_id'           => $client->id,
                'service_type_id'     => $serviceId,
                'description'         => $pData['description'],
                'start_date'          => $pData['start_date'],
                'deadline'            => $pData['deadline'],
                'priority'            => $pData['priority'],
                'status_id'           => $statusId,
                'manager_id'          => $manager?->id ?? 1,
                'budget'              => $pData['budget'],
                'progress_percentage' => $pData['progress'],
                'payment_status'      => $pData['progress'] == 100 ? 'paid' : 'unpaid',
                'created_by'          => 1,
            ]);

            // Attach team member
            if ($member) {
                $project->teamMembers()->syncWithoutDetaching([
                    $member->id => ['role' => 'designer', 'assigned_at' => now()],
                ]);
            }

            // Add sample tasks
            if ($taskStatusTodo && $taskStatusDone && $taskStatusIP) {
                $sampleTasks = [
                    ['title' => 'اجتماع البريفينج مع العميل',   'status' => $taskStatusDone, 'priority' => 'high',   'days' => -15],
                    ['title' => 'إعداد مستند الاحتياجات',        'status' => $taskStatusDone, 'priority' => 'high',   'days' => -12],
                    ['title' => 'التصميم الأولي',                 'status' => $taskStatusIP,   'priority' => 'high',   'days' => 5],
                    ['title' => 'مراجعة داخلية',                 'status' => $taskStatusTodo, 'priority' => 'medium', 'days' => 8],
                    ['title' => 'تسليم النسخة النهائية للعميل',  'status' => $taskStatusTodo, 'priority' => 'high',   'days' => 12],
                ];

                foreach ($sampleTasks as $t) {
                    Task::updateOrCreate([
                        'project_id' => $project->id,
                        'title'      => $t['title'],
                    ], [
                        'project_id'  => $project->id,
                        'title'       => $t['title'],
                        'status_id'   => $t['status']->id,
                        'priority'    => $t['priority'],
                        'assigned_to' => $member?->id,
                        'due_date'    => now()->addDays($t['days'])->toDateString(),
                        'created_by'  => 1,
                    ]);
                }
            }
        }
    }
}
