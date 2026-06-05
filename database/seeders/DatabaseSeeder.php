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
            DemoBusinessSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('🎉 Air Menu | Workspace seeded successfully!');
        $this->command->info('');
        $this->command->info('Demo credentials only (not for production):');
        $this->command->info('  Shared demo password: ' . UserSeeder::DEMO_PASSWORD);
        $this->command->info('  Super Admin : admin@airmenu.net');
        $this->command->info('  Admin       : mohamed@airmenu.net');
        $this->command->info('  PM          : sara@airmenu.net');
        $this->command->info('  Team        : ahmed@airmenu.net');
        $this->command->info('  Dev         : dev@workspace.com');
        $this->command->info('  Finance     : fatima@airmenu.net');
        $this->command->info('');
    }
}
