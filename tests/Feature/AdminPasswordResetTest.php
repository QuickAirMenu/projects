<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'reset-admin@example.com', 'password' => 'OldPassword123',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_login_page_has_forgot_password_link(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'), false)
            ->assertSee(__('auth.forgot_password'));
    }

    public function test_reset_link_is_sent_with_custom_notification(): void
    {
        Notification::fake();
        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => 'smtp.example.com', 'type' => 'string', 'group' => 'mail']);

        $this->post(route('password.email'), ['email' => $this->admin->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($this->admin, ResetPasswordNotification::class);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $this->admin->email]);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $token = Password::broker()->createToken($this->admin);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $this->admin->email,
            'password' => 'NewStrongPass123',
            'password_confirmation' => 'NewStrongPass123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewStrongPass123', $this->admin->fresh()->password));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => $this->admin->email,
            'password' => 'NewStrongPass123',
            'password_confirmation' => 'NewStrongPass123',
        ])->assertSessionHasErrors('email');
    }
}
