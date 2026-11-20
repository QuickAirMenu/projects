<?php

namespace Tests\Feature;

use App\Mail\UserInvitationMail;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'inv-admin@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_create_with_invite_persists_token_sends_mail_and_shows_link(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => 'smtp.example.com', 'type' => 'string', 'group' => 'mail']);

        $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Employee One',
            'email' => 'employee1@example.com',
            'role' => 'team-member',
            'language' => 'ar',
            'send_invite' => 1,
        ])->assertRedirect(route('users.index'))
            ->assertSessionHas('invite_link');

        $user = User::where('email', 'employee1@example.com')->firstOrFail();
        $this->assertNotNull($user->invitation_token);
        $this->assertNotNull($user->invitation_expires_at);
        Mail::assertSent(UserInvitationMail::class, fn ($mail) => $mail->hasTo('employee1@example.com'));

        $this->actingAs($this->admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee($user->invitation_token);
    }

    public function test_invitation_setup_completes_and_clears_token(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => 'smtp.example.com', 'type' => 'string', 'group' => 'mail']);

        $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Employee Two', 'email' => 'employee2@example.com', 'role' => 'team-member', 'send_invite' => 1,
        ]);

        $user = User::where('email', 'employee2@example.com')->firstOrFail();

        $this->get(route('invitation.setup', $user->invitation_token))->assertOk();

        $this->post(route('invitation.setup.post', $user->invitation_token), [
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
        ])->assertRedirect(route('login'));

        $user->refresh();
        $this->assertNull($user->invitation_token);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_invite_falls_back_to_manual_link_when_mail_not_configured(): void
    {
        Mail::fake();
        Setting::where('key', 'mail_host')->delete();
        config(['mail.mailers.smtp.host' => null]);

        $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Employee Three', 'email' => 'employee3@example.com', 'role' => 'team-member', 'send_invite' => 1,
        ])->assertRedirect(route('users.index'))
            ->assertSessionHas('error')
            ->assertSessionHas('invite_link');

        $user = User::where('email', 'employee3@example.com')->firstOrFail();
        $this->assertNotNull($user->invitation_token);
        Mail::assertNothingSent();
    }
}
