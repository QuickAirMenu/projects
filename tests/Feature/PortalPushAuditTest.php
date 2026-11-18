<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientPortalAccount;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PortalPushAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected ClientPortalAccount $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        config([
            'webpush.public_key' => 'test-public-key',
            'webpush.private_key' => 'test-private-key',
            'webpush.subject' => 'mailto:test@example.com',
        ]);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'audit2@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $client = Client::create([
            'name' => 'Portal Client', 'email' => 'portal-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->account = ClientPortalAccount::create([
            'client_id' => $client->id, 'password' => 'secret-password',
            'is_active' => true, 'email_verified_at' => now(),
        ]);
    }

    public function test_portal_push_subscribe_and_unsubscribe(): void
    {
        $this->actingAs($this->account, 'client')
            ->getJson(route('portal.push.key'))
            ->assertOk()
            ->assertJsonPath('publicKey', 'test-public-key');

        $payload = [
            'endpoint' => 'https://push.example.com/portal-1',
            'keys' => ['p256dh' => 'pkey', 'auth' => 'authkey'],
            'contentEncoding' => 'aesgcm',
        ];

        $this->actingAs($this->account, 'client')->postJson(route('portal.push.subscribe'), $payload)->assertOk();
        $this->assertSame(1, $this->account->pushSubscriptions()->count());

        $this->actingAs($this->account, 'client')
            ->deleteJson(route('portal.push.unsubscribe'), ['endpoint' => $payload['endpoint']])
            ->assertOk();
        $this->assertSame(0, $this->account->pushSubscriptions()->count());
    }

    public function test_audit_log_is_visible_for_admins_and_filterable(): void
    {
        activity()->causedBy($this->admin)->log('تم تعديل الفاتورة');

        $this->actingAs($this->admin)
            ->get(route('settings.audit-log'))
            ->assertOk()
            ->assertSee('تم تعديل الفاتورة')
            ->assertSee($this->admin->name);

        $this->actingAs($this->admin)
            ->get(route('settings.audit-log', ['q' => 'غير موجود']))
            ->assertOk()
            ->assertDontSee('تم تعديل الفاتورة');

        $this->assertGreaterThanOrEqual(1, Activity::count());
    }

    public function test_audit_log_requires_settings_permission(): void
    {
        $user = User::create([
            'name' => 'Member', 'email' => 'member@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $user->assignRole('team-member');

        $this->actingAs($user)->get(route('settings.audit-log'))->assertForbidden();
    }
}
