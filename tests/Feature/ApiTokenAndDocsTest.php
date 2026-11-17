<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ApiTokenAndDocsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'tokens@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_user_can_view_tokens_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('api-tokens.index'))
            ->assertOk()
            ->assertSee(__('settings.api_tokens'));
    }

    public function test_user_can_create_and_revoke_token(): void
    {
        $this->actingAs($this->admin)
            ->post(route('api-tokens.store'), ['name' => 'Mobile app'])
            ->assertRedirect(route('api-tokens.index'))
            ->assertSessionHas('plain_text_token');

        $this->assertCount(1, $this->admin->tokens()->get());
        $token = $this->admin->tokens()->first();
        $this->assertSame('Mobile app', $token->name);
        $this->assertNotNull($token->expires_at);
        $this->assertTrue($token->expires_at->greaterThan(now()->addDays(29)));

        $this->actingAs($this->admin)
            ->delete(route('api-tokens.destroy', $token->id))
            ->assertRedirect();

        $this->assertCount(0, $this->admin->tokens()->get());
    }

    public function test_openapi_spec_is_public_and_complete(): void
    {
        $response = $this->getJson(route('api.openapi'))->assertOk();

        $response->assertJsonPath('openapi', '3.0.3');
        $paths = $response->json('paths');
        $this->assertArrayHasKey('/auth/login', $paths);
        $this->assertArrayHasKey('/invoices', $paths);
        $this->assertArrayHasKey('/tasks', $paths);
    }

    public function test_docs_page_renders(): void
    {
        $this->get(route('api.docs'))->assertOk()->assertSee('swagger-ui');
    }
}
