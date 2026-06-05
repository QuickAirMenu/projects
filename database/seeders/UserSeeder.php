<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'System Administrator', 'email' => 'admin@airmenu.net', 'role' => 'super-admin', 'job_title' => 'System Administrator', 'department' => 'IT', 'phone' => '+966500000000'],
            ['name' => 'محمد أحمد', 'email' => 'mohamed@airmenu.net', 'role' => 'admin', 'job_title' => 'General Manager', 'department' => 'Management', 'phone' => '+966501111111'],
            ['name' => 'سارة خالد', 'email' => 'sara@airmenu.net', 'role' => 'project-manager', 'job_title' => 'Project Manager', 'department' => 'Projects', 'phone' => '+966502222222'],
            ['name' => 'أحمد علي', 'email' => 'ahmed@airmenu.net', 'role' => 'team-member', 'job_title' => 'Graphic Designer', 'department' => 'Design', 'phone' => '+966503333333'],
            ['name' => 'خالد محمود', 'email' => 'dev@workspace.com', 'role' => 'team-member', 'job_title' => 'Web Developer', 'department' => 'Development', 'phone' => '+966504444444'],
            ['name' => 'فاطمة عبدالله', 'email' => 'fatima@airmenu.net', 'role' => 'finance', 'job_title' => 'Financial Manager', 'department' => 'Finance', 'phone' => '+966505555555'],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'language' => 'ar',
                    'timezone' => 'Asia/Riyadh',
                    'email_verified_at' => now(),
                ])
            );
            $user->syncRoles([$role]);
        }

        $this->command->info('Users created successfully!');
    }
}
