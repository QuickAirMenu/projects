<?php

namespace Tests\Feature;

use App\Mail\UserInvitationMail;
use App\Models\Setting;
use App\Models\User;
use App\Services\EmailVerificationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmailValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'validator@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_service_detects_typos_and_invalid_formats(): void
    {
        $service = app(EmailVerificationService::class);

        $typo = $service->inspect('Ahmed@gmal.com');
        $this->assertFalse($typo['ok']);
        $this->assertSame('gmail.com', $typo['suggestion']);

        $valid = $service->inspect('ahmed@example.com');
        $this->assertTrue($valid['ok']);
        $this->assertNull($valid['suggestion']);

        $invalid = $service->inspect('not-an-email');
        $this->assertFalse($invalid['ok']);
        $this->assertFalse($invalid['valid_format']);
    }

    public function test_live_email_check_endpoint_returns_suggestion(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('users.validate-email'), ['email' => 'Ahmed@gmal.com'])
            ->assertOk()
            ->assertJsonPath('ok', false)
            ->assertJsonPath('suggestion', 'gmail.com');
    }

    public function test_user_creation_is_blocked_for_bad_domain(): void
    {
        $mock = Mockery::mock(EmailVerificationService::class);
        $mock->shouldReceive('inspect')->andReturn(['ok' => false, 'message' => 'Bad domain']);
        $this->app->instance(EmailVerificationService::class, $mock);

        $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Bad Email', 'email' => 'bad@unknown-domain-xyz.test', 'role' => 'team-member',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'bad@unknown-domain-xyz.test']);
    }

    public function test_invitation_is_not_sent_for_invalid_domain(): void
    {
        Mail::fake();
        $user = User::create([
            'name' => 'Invitee', 'email' => 'invitee@gmal.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);

        $mock = Mockery::mock(EmailVerificationService::class);
        $mock->shouldReceive('inspect')->andReturn(['ok' => false, 'message' => 'Did you mean gmail.com?']);
        $this->app->instance(EmailVerificationService::class, $mock);

        $this->actingAs($this->admin)
            ->post(route('users.invite', $user))
            ->assertSessionHas('error');

        Mail::assertNothingSent();
        $this->assertNull($user->fresh()->invitation_token);
    }

    public function test_valid_invitation_persists_token_and_sends(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => 'smtp.example.com', 'type' => 'string', 'group' => 'mail']);

        $mock = Mockery::mock(EmailVerificationService::class);
        $mock->shouldReceive('inspect')->andReturn(['ok' => true, 'message' => null, 'suggestion' => null]);
        $this->app->instance(EmailVerificationService::class, $mock);

        $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Good Email', 'email' => 'good@company.example', 'role' => 'team-member', 'send_invite' => 1,
        ])->assertRedirect(route('users.index'))->assertSessionHas('success');

        $created = User::where('email', 'good@company.example')->firstOrFail();
        $this->assertNotNull($created->invitation_token);
        Mail::assertSent(UserInvitationMail::class);
    }
}
