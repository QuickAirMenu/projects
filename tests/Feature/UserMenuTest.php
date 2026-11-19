<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserMenuTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'System Administrator', 'email' => 'um@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_header_user_button_is_icon_only(): void
    {
        $header = file_get_contents(resource_path('views/layouts/partials/header.blade.php'));

        $this->assertStringNotContainsString('<span class="text-sm font-medium hidden md:block">{{ auth()->user()->name }}</span>', $header);
        $this->assertStringContainsString('aria-label="{{ auth()->user()->name }}"', $header);
    }

    public function test_dashboard_renders_without_errors(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))->assertOk();
    }
}
