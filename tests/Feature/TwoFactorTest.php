<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => '2fa@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_enable_and_confirm_flow(): void
    {
        $this->actingAs($this->admin)->post(route('two-factor.enable'))->assertRedirect(route('two-factor.index'));

        $this->admin->refresh();
        $this->assertNotNull($this->admin->two_factor_secret);
        $this->assertNull($this->admin->two_factor_confirmed_at);

        $this->actingAs($this->admin)->get(route('two-factor.index'))->assertOk()->assertSee($this->admin->two_factor_secret);

        $code = (new Google2FA)->getCurrentOtp($this->admin->two_factor_secret);

        $this->actingAs($this->admin)
            ->post(route('two-factor.confirm'), ['code' => $code])
            ->assertRedirect(route('two-factor.index'))
            ->assertSessionHas('recovery_codes');

        $this->admin->refresh();
        $this->assertTrue($this->admin->hasTwoFactorEnabled());
        $this->assertCount(8, $this->admin->recoveryCodes());
    }

    public function test_login_requires_valid_second_factor(): void
    {
        $secret = $this->enableTwoFactor($this->admin);

        $this->post(route('logout'));

        $this->post(route('login'), ['email' => '2fa@example.com', 'password' => 'password'])
            ->assertRedirect(route('two-factor.login'));

        $this->assertGuest();
        $this->get(route('two-factor.login'))->assertOk();

        $this->post(route('two-factor.login.store'), ['code' => '000000'])
            ->assertSessionHasErrors('code');
        $this->assertGuest();

        $code = (new Google2FA)->getCurrentOtp($secret);

        $this->post(route('two-factor.login.store'), ['code' => $code])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_recovery_code_works_only_once(): void
    {
        $this->enableTwoFactor($this->admin);
        $code = $this->admin->recoveryCodes()[0];

        $this->withSession(['2fa_user_id' => $this->admin->id, '2fa_started_at' => time()])
            ->post(route('two-factor.login.store'), ['code' => $code])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->admin);

        auth()->logout();

        $this->withSession(['2fa_user_id' => $this->admin->id, '2fa_started_at' => time()])
            ->post(route('two-factor.login.store'), ['code' => $code])
            ->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_api_login_requires_code_when_enabled(): void
    {
        $secret = $this->enableTwoFactor($this->admin);

        $this->postJson('/api/v1/auth/login', [
            'email' => '2fa@example.com',
            'password' => 'password',
        ])->assertStatus(422)->assertJsonValidationErrors('code');

        $this->postJson('/api/v1/auth/login', [
            'email' => '2fa@example.com',
            'password' => 'password',
            'code' => (new Google2FA)->getCurrentOtp($secret),
        ])->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->admin->update(['is_active' => false]);

        $this->post(route('login'), ['email' => '2fa@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_disable_requires_password_and_code(): void
    {
        $secret = $this->enableTwoFactor($this->admin);

        $this->actingAs($this->admin)
            ->delete(route('two-factor.disable'), ['password' => 'wrong', 'code' => '123456'])
            ->assertSessionHas('error');

        $this->assertTrue($this->admin->fresh()->hasTwoFactorEnabled());

        $this->actingAs($this->admin)
            ->delete(route('two-factor.disable'), [
                'password' => 'password',
                'code' => (new Google2FA)->getCurrentOtp($secret),
            ])
            ->assertRedirect(route('two-factor.index'));

        $this->assertFalse($this->admin->fresh()->hasTwoFactorEnabled());
    }

    protected function enableTwoFactor(User $user): string
    {
        $secret = (new Google2FA)->generateSecretKey(32);

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();
        $user->replaceRecoveryCodes();

        return $secret;
    }
}
