<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'nav@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_sidebar_is_condensed_and_timesheets_moved_to_projects(): void
    {
        $html = $this->actingAs($this->admin)->get(route('dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString(route('accounts.index'), $html);
        $this->assertStringContainsString(route('time-entries.index'), $html);
        // recurring invoices removed from sidebar
        $this->assertStringNotContainsString(route('recurring-invoices.index'), $html);
    }

    public function test_recurring_invoices_reachable_from_invoices_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('invoices.index'))
            ->assertOk()
            ->assertSee(route('recurring-invoices.index'), false);
    }

    public function test_theme_css_version_bumped_and_accent_overrides_present(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertSee('dark.css?v=4', false);

        $css = file_get_contents(public_path('css/dark.css'));
        $this->assertStringContainsString('Refined accent palette', $css);
        $this->assertStringContainsString('.bg-red-100', $css);
        $this->assertStringContainsString('.text-red-600', $css);
    }
}
