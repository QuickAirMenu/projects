<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            ServiceTypeSeeder::class,
            ProjectStatusSeeder::class,
            TaskStatusSeeder::class,
            SettingSeeder::class,
            DemoClientSeeder::class,
            DemoProjectSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('🎉 Air Menu | Workspace seeded successfully!');
        $this->command->info('');
        $this->command->info('Default credentials:');
        $this->command->info('  Super Admin : superadmin@workspace.com / password');
        $this->command->info('  Admin       : admin@workspace.com / password');
        $this->command->info('  PM          : pm@workspace.com / password');
        $this->command->info('  Team        : team@workspace.com / password');
        $this->command->info('  Finance     : finance@workspace.com / password');
        $this->command->info('');
    }
}
