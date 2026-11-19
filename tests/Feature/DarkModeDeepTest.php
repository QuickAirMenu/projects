<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DarkModeDeepTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'darkdeep@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_admin_header_has_visible_toggle_and_versioned_css(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('dark.css?v=3', false)
            ->assertSee('data-theme-toggle', false)
            ->assertSee('id="themeToggle"', false);
    }

    public function test_login_page_respects_theme_preference(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('css/dark.css', false)
            ->assertSee('ip_theme', false)
            ->assertSee('data-theme-toggle', false);
    }

    public function test_dark_css_includes_print_and_focus_and_extended_tokens(): void
    {
        $css = file_get_contents(public_path('css/dark.css'));

        $this->assertStringContainsString('@media print', $css);
        $this->assertStringContainsString(':focus-visible', $css);
        $this->assertStringContainsString('.bg-gray-200', $css);
        $this->assertStringContainsString('.text-slate-600', $css);
        $this->assertStringContainsString('::-webkit-scrollbar', $css);
        $this->assertStringContainsString('portal-sidebar-body', $css);
    }
}
