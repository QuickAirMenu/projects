<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // User Management
            'manage-users',
            'manage-roles',

            // Client Management
            'view-clients',
            'create-clients',
            'edit-clients',
            'delete-clients',

            // Project Management
            'view-projects',
            'create-projects',
            'edit-projects',
            'delete-projects',
            'archive-projects',

            // Task Management
            'view-tasks',
            'create-tasks',
            'edit-tasks',
            'delete-tasks',
            'assign-tasks',

            // Financial
            'view-quotes',
            'create-quotes',
            'edit-quotes',
            'delete-quotes',
            'send-quotes',

            'view-contracts',
            'create-contracts',
            'edit-contracts',
            'delete-contracts',

            'view-invoices',
            'create-invoices',
            'edit-invoices',
            'delete-invoices',

            'view-payments',
            'create-payments',
            'delete-payments',

            // Change Requests
            'view-change-requests',
            'create-change-requests',
            'approve-change-requests',

            // Reports
            'view-reports',
            'view-financials',

            // Settings
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles and Assign Permissions

        // Super Admin - All Permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - Most Permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'view-clients', 'create-clients', 'edit-clients', 'delete-clients',
            'view-projects', 'create-projects', 'edit-projects', 'delete-projects', 'archive-projects',
            'view-tasks', 'create-tasks', 'edit-tasks', 'delete-tasks', 'assign-tasks',
            'view-quotes', 'create-quotes', 'edit-quotes', 'delete-quotes', 'send-quotes',
            'view-contracts', 'create-contracts', 'edit-contracts', 'delete-contracts',
            'view-invoices', 'create-invoices', 'edit-invoices', 'delete-invoices',
            'view-payments', 'create-payments', 'delete-payments',
            'view-change-requests', 'create-change-requests', 'approve-change-requests',
            'view-reports', 'view-financials',
        ]);

        // Project Manager
        $projectManager = Role::firstOrCreate(['name' => 'project-manager']);
        $projectManager->givePermissionTo([
            'view-clients', 'create-clients', 'edit-clients',
            'view-projects', 'create-projects', 'edit-projects',
            'view-tasks', 'create-tasks', 'edit-tasks', 'delete-tasks', 'assign-tasks',
            'view-quotes',
            'view-contracts',
            'view-invoices',
            'view-payments',
            'view-change-requests', 'create-change-requests',
            'view-reports',
        ]);

        // Team Member
        $teamMember = Role::firstOrCreate(['name' => 'team-member']);
        $teamMember->givePermissionTo([
            'view-clients',
            'view-projects',
            'view-tasks', 'edit-tasks',
            'view-contracts',
            'view-change-requests',
        ]);

        // Finance
        $finance = Role::firstOrCreate(['name' => 'finance']);
        $finance->givePermissionTo([
            'view-clients',
            'view-projects',
            'view-quotes', 'create-quotes', 'edit-quotes', 'send-quotes',
            'view-contracts', 'create-contracts', 'edit-contracts',
            'view-invoices', 'create-invoices', 'edit-invoices',
            'view-payments', 'create-payments',
            'view-reports', 'view-financials',
        ]);

        // Client (for future use)
        $client = Role::firstOrCreate(['name' => 'client']);
        $client->givePermissionTo([
            'view-projects',
            'view-tasks',
            'view-invoices',
        ]);

        $this->command->info('Roles and Permissions created successfully!');
    }
}
