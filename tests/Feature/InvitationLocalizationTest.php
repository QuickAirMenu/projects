<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InvitationLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->user = User::create([
            'name' => 'Invitee Localized', 'email' => 'invitee-localized@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'ar',
            'invitation_token' => 'localtoken123', 'invitation_expires_at' => now()->addDay(),
        ]);
    }

    public function test_invitation_page_is_translated_in_both_locales(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get(route('invitation.setup', 'localtoken123'))
            ->assertOk()
            ->assertSee(__('invitation.heading', [], 'en'))
            ->assertSee(__('invitation.submit', [], 'en'))
            ->assertDontSee('invitation.');

        $this->withSession(['locale' => 'ar'])
            ->get(route('invitation.setup', 'localtoken123'))
            ->assertOk()
            ->assertSee(__('invitation.heading', [], 'ar'))
            ->assertSee(__('invitation.submit', [], 'ar'));
    }

    public function test_invalid_invitation_redirects_to_login_with_error(): void
    {
        $this->get(route('invitation.setup', 'missing-token'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }
}
