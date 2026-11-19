<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class VersionFooterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'ver@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_footer_shows_version_from_config(): void
    {
        config(['app.version' => '9.9.9', 'app.build' => 'abc1234']);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('v9.9.9')
            ->assertSee('abc1234');
    }

    public function test_no_hardcoded_version_remains(): void
    {
        $this->assertStringNotContainsString('v1.0.0', file_get_contents(resource_path('views/layouts/partials/footer.blade.php')));
        $this->assertStringNotContainsString('v1.0.0', file_get_contents(resource_path('views/portal/layouts/app.blade.php')));
    }
}
