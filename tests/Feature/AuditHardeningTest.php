<?php

namespace Tests\Feature;

use App\Listeners\SendPushNotification;
use App\Models\Client;
use App\Models\ClientPortalAccount;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\PushSubscription;
use App\Models\RecurringInvoice;
use App\Models\ServiceType;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\RecurringInvoiceService;
use App\Services\WebPushService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuditHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        Storage::fake('public');
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'audit@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $this->client = Client::create([
            'name' => 'Audit Client', 'email' => 'audit-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
    }

    public function test_push_listener_ignores_non_database_channels(): void
    {
        $mock = Mockery::mock(WebPushService::class);
        $mock->shouldNotReceive('send');
        $this->app->instance(WebPushService::class, $mock);

        (new SendPushNotification)->handle(new NotificationSent($this->admin, new AuditFakeNotification, 'mail'));
        $this->assertTrue(true);
    }

    public function test_deleting_ticket_removes_attachment_files(): void
    {
        $account = ClientPortalAccount::create([
            'client_id' => $this->client->id, 'password' => 'secret-password',
            'is_active' => true, 'email_verified_at' => now(),
        ]);
        $ticket = SupportTicket::create([
            'client_id' => $this->client->id, 'portal_account_id' => $account->id,
            'subject' => 'Cleanup', 'message' => 'x', 'priority' => 'low', 'status' => 'open',
        ]);
        Storage::disk('private')->put('tickets/'.$ticket->id.'/a.pdf', 'data');
        $attachment = $ticket->attachments()->create([
            'file_name' => 'a.pdf', 'file_path' => 'tickets/'.$ticket->id.'/a.pdf',
            'file_size' => 4, 'mime' => 'application/pdf',
        ]);

        $ticket->delete();

        $this->assertDatabaseMissing('support_ticket_attachments', ['id' => $attachment->id]);
        Storage::disk('private')->assertMissing('tickets/'.$ticket->id.'/a.pdf');
    }

    public function test_svg_logo_upload_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->put(route('settings.update'), [
                'company_logo' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
            ])
            ->assertSessionHasErrors('company_logo');
    }

    public function test_web_logout_removes_push_subscriptions(): void
    {
        PushSubscription::create([
            'notifiable_type' => $this->admin->getMorphClass(),
            'notifiable_id' => $this->admin->id,
            'endpoint_hash' => hash('sha256', 'https://push.example.com/1'),
            'endpoint' => 'https://push.example.com/1',
            'public_key' => 'k', 'auth_token' => 'a', 'content_encoding' => 'aesgcm',
        ]);

        $this->actingAs($this->admin)->post(route('logout'))->assertRedirect(route('login'));

        $this->assertSame(0, $this->admin->pushSubscriptions()->count());
    }

    public function test_token_creation_is_capped_at_ten(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->admin->createToken('token-'.$i);
        }

        $this->actingAs($this->admin)
            ->post(route('api-tokens.store'), ['name' => 'one more'])
            ->assertSessionHas('error');

        $this->assertSame(10, $this->admin->tokens()->count());
    }

    public function test_api_routes_are_rate_limited(): void
    {
        $route = collect(Route::getRoutes())->first(fn ($r) => $r->uri() === 'api/v1/clients');
        $this->assertNotNull($route);
        $this->assertContains('throttle:60,1', $route->gatherMiddleware());
    }

    public function test_internal_routes_are_rate_limited(): void
    {
        foreach (['internal/backup', 'internal/recurring'] as $uri) {
            $route = collect(Route::getRoutes())->first(fn ($r) => $r->uri() === $uri);
            $this->assertNotNull($route, $uri);
            $this->assertContains('throttle:5,1', $route->gatherMiddleware(), $uri);
        }
    }

    public function test_csp_report_only_header_is_present(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertHeader('Content-Security-Policy-Report-Only');
    }

    public function test_recurring_generation_skips_when_not_due_unless_forced(): void
    {
        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-audit',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $project = Project::create([
            'name' => 'Audit Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
        $recurring = RecurringInvoice::create([
            'client_id' => $this->client->id, 'project_id' => $project->id, 'name' => 'Future',
            'invoice_type' => 'additional',
            'items' => [['description' => 'x', 'quantity' => 1, 'unit_price' => 10]],
            'tax_percentage' => 0, 'discount_type' => 'fixed', 'discount_amount' => 0,
            'frequency' => 'monthly', 'next_run_date' => now()->addDays(10)->toDateString(),
            'is_active' => true, 'created_by' => $this->admin->id,
        ]);

        $invoice = app(RecurringInvoiceService::class)->generate($recurring, false);

        $this->assertNull($invoice);
        $this->assertSame(0, Invoice::count());
    }
}

class AuditFakeNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => 'x', 'message' => 'y', 'url' => '/'];
    }
}
