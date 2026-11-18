<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ClientSourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'source@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_client_can_be_created_with_email_campaign_source(): void
    {
        $this->actingAs($this->admin)->post(route('clients.store'), [
            'name' => 'Campaign Lead',
            'company_name' => 'Mail Co',
            'email' => 'lead@campaign.test',
            'phone' => '0500000000',
            'status' => 'active',
            'source' => 'email_campaign',
        ])->assertRedirect();

        $client = Client::firstOrFail();
        $this->assertSame('email_campaign', $client->source);
        $this->assertSame(__('client.source_email_campaign'), $client->sourceLabel());

        $this->actingAs($this->admin)
            ->get(route('clients.edit', $client))
            ->assertOk()
            ->assertSee('value="email_campaign"', false);

        $this->actingAs($this->admin)
            ->get(route('clients.show', $client))
            ->assertOk()
            ->assertSee(__('client.source_email_campaign'));
    }

    public function test_email_campaign_option_is_available_on_create_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('clients.create'))
            ->assertOk()
            ->assertSee('value="email_campaign"', false);
    }
}
