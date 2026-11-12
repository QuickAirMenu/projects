<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        User::create([
            'name' => 'Admin', 'email' => 'pwa@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ])->assignRole('super-admin');
    }

    public function test_manifest_is_served_with_company_name(): void
    {
        Setting::updateOrCreate(['key' => 'company_name_ar'], ['value' => 'آير منيو', 'group' => 'general']);

        $response = $this->get('/manifest.webmanifest');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/manifest+json');

        $data = $response->json();
        $this->assertSame('آير منيو', $data['name']);
        $this->assertSame('standalone', $data['display']);
        $this->assertCount(2, $data['icons']);
    }

    public function test_login_page_declares_pwa_manifest(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('serviceWorker', false);
    }
}
