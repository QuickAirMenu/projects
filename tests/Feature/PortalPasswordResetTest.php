<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientPortalAccount;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PortalPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected Client $client;

    protected ClientPortalAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = User::create([
            'name' => 'Admin', 'email' => 'reset@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $admin->assignRole('super-admin');

        $this->client = Client::create([
            'name' => 'Reset Client', 'email' => 'reset-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $admin->id,
        ]);
        $this->account = ClientPortalAccount::create([
            'client_id' => $this->client->id, 'password' => 'old-password',
            'is_active' => true, 'email_verified_at' => now(),
        ]);
    }

    public function test_unknown_email_returns_same_response_without_sending(): void
    {
        Mail::fake();

        $this->post(route('portal.forgot-password.post'), ['email' => 'nobody@example.com'])
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertNothingSent();
    }

    public function test_known_email_stores_hashed_token(): void
    {
        Mail::fake();

        $this->post(route('portal.forgot-password.post'), ['email' => $this->client->email])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->account->refresh();
        $this->assertNotNull($this->account->reset_token_hash);
        $this->assertNotNull($this->account->reset_token_expires_at);
    }

    public function test_valid_token_allows_password_reset(): void
    {
        $token = 'test-reset-token-123456';
        $this->account->update([
            'reset_token_hash' => hash_hmac('sha256', $token, (string) config('app.key')),
            'reset_token_expires_at' => now()->addHour(),
        ]);

        $this->get(route('portal.reset-password', $token))->assertOk();

        $this->post(route('portal.reset-password.post', $token), [
            'password' => 'new-strong-pass',
            'password_confirmation' => 'new-strong-pass',
        ])->assertRedirect(route('portal.login'));

        $this->assertTrue(Hash::check('new-strong-pass', $this->account->fresh()->password));
        $this->assertNull($this->account->fresh()->reset_token_hash);
    }

    public function test_expired_or_invalid_token_rejected(): void
    {
        $this->account->update([
            'reset_token_hash' => hash_hmac('sha256', 'expired-token', (string) config('app.key')),
            'reset_token_expires_at' => now()->subMinute(),
        ]);

        $this->get(route('portal.reset-password', 'expired-token'))->assertNotFound();
        $this->get(route('portal.reset-password', 'wrong-token'))->assertNotFound();
    }
}
