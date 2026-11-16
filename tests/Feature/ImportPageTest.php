<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ImportPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'imports@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_imports_page_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('imports.index'))
            ->assertOk()
            ->assertSee('Import data');
    }

    public function test_import_templates_download(): void
    {
        $this->actingAs($this->admin)
            ->get(route('imports.clients.template'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('imports.projects.template'))
            ->assertOk();
    }
}
