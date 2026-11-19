<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PrivacyModeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'privacy@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_privacy_toggle_and_automask_are_present(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('privacy-mask-js', false)
            ->assertSee('id="privacyIcon"', false)
            ->assertSee('privacyMode', false)
            ->assertSee('dark.css?v=5', false);
    }

    public function test_privacy_css_prevents_hover_reveal(): void
    {
        $css = file_get_contents(public_path('css/dark.css'));

        $this->assertStringContainsString('html.privacy-on .privacy-mask', $css);
        $this->assertStringContainsString('.privacy-mask-js', $css);
        $this->assertStringContainsString('blur(7px) !important', $css);
    }

    public function test_privacy_boot_flag_is_in_head(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $this->assertStringContainsString('ip_privacy_boot', $layout);
        $this->assertStringContainsString('privacyMode', $layout);
    }
}
