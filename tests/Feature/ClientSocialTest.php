<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ClientSocialTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'social@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_client_can_store_website_and_social_links_with_normalization(): void
    {
        $this->actingAs($this->admin)->post(route('clients.store'), [
            'name' => 'Social Lead',
            'company_name' => 'Acme',
            'status' => 'active',
            'website' => 'acme.example.com',
            'linkedin' => 'linkedin.com/company/acme',
            'twitter' => 'https://x.com/acme',
            'instagram' => '',
            'facebook' => 'facebook.com/acme',
        ])->assertRedirect();

        $client = Client::firstOrFail();
        $this->assertSame('https://acme.example.com', $client->website);
        $this->assertSame('https://linkedin.com/company/acme', $client->linkedin);
        $this->assertSame('https://x.com/acme', $client->twitter);
        $this->assertNull($client->instagram);
        $this->assertSame('https://facebook.com/acme', $client->facebook);

        $this->actingAs($this->admin)
            ->get(route('clients.edit', $client))
            ->assertOk()
            ->assertSee('name="website"', false)
            ->assertSee('name="linkedin"', false);

        $this->actingAs($this->admin)
            ->get(route('clients.show', $client))
            ->assertOk()
            ->assertSee('acme.example.com')
            ->assertSee('LinkedIn');
    }

    public function test_create_page_has_social_fields(): void
    {
        $this->actingAs($this->admin)
            ->get(route('clients.create'))
            ->assertOk()
            ->assertSee('name="website"', false)
            ->assertSee('name="instagram"', false);
    }
}
