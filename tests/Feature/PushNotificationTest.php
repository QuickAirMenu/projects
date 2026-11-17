<?php

namespace Tests\Feature;

use App\Listeners\SendPushNotification;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\WebPushService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\Notification;
use Mockery;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->user = User::create([
            'name' => 'Admin', 'email' => 'push@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->user->assignRole('super-admin');

        config([
            'webpush.public_key' => 'test-public-key',
            'webpush.private_key' => 'test-private-key',
            'webpush.subject' => 'mailto:test@example.com',
        ]);
    }

    public function test_public_key_endpoint_returns_key(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('push.key'))
            ->assertOk()
            ->assertJsonPath('publicKey', 'test-public-key');
    }

    public function test_subscribe_upserts_subscription_and_unsubscribe_removes_it(): void
    {
        $payload = [
            'endpoint' => 'https://push.example.com/abc',
            'keys' => ['p256dh' => 'pkey', 'auth' => 'authkey'],
            'contentEncoding' => 'aesgcm',
        ];

        $this->actingAs($this->user)->postJson(route('push.subscribe'), $payload)->assertOk();
        $this->assertSame(1, $this->user->pushSubscriptions()->count());

        $this->actingAs($this->user)->postJson(route('push.subscribe'), $payload)->assertOk();
        $this->assertSame(1, $this->user->pushSubscriptions()->count());

        $this->actingAs($this->user)
            ->deleteJson(route('push.unsubscribe'), ['endpoint' => $payload['endpoint']])
            ->assertOk();

        $this->assertSame(0, $this->user->pushSubscriptions()->count());
    }

    public function test_listener_sends_push_with_notification_payload(): void
    {
        PushSubscription::create([
            'notifiable_type' => $this->user->getMorphClass(),
            'notifiable_id' => $this->user->id,
            'endpoint_hash' => hash('sha256', 'https://push.example.com/abc'),
            'endpoint' => 'https://push.example.com/abc',
            'public_key' => 'pkey',
            'auth_token' => 'authkey',
            'content_encoding' => 'aesgcm',
        ]);

        $mock = Mockery::mock(WebPushService::class);
        $mock->shouldReceive('send')->once()
            ->withArgs(function ($notifiable, array $payload) {
                return $notifiable->is($this->user)
                    && $payload['title'] === 'Payment received'
                    && $payload['body'] === 'INV-100 paid'
                    && $payload['url'] === '/payments';
            })->andReturn(1);
        $this->app->instance(WebPushService::class, $mock);

        (new SendPushNotification)->handle(new NotificationSent($this->user, new FakePushNotification, 'database'));
        $this->assertTrue(true);
    }

    public function test_listener_skips_users_without_subscriptions(): void
    {
        $mock = Mockery::mock(WebPushService::class);
        $mock->shouldNotReceive('send');
        $this->app->instance(WebPushService::class, $mock);

        (new SendPushNotification)->handle(new NotificationSent($this->user, new FakePushNotification, 'database'));
        $this->assertTrue(true);
    }
}

class FakePushNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment received',
            'message' => 'INV-100 paid',
            'url' => '/payments',
        ];
    }
}
