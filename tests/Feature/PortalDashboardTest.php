<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientPortalAccount;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Quote;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PortalDashboardTest extends TestCase
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
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_active' => true,
            'language' => 'en',
        ]);
        $admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);

        $this->client = Client::create([
            'name' => 'Portal Client',
            'email' => 'portal@example.com',
            'phone' => '0500000000',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->account = ClientPortalAccount::create([
            'client_id' => $this->client->id,
            'password' => 'secret-password',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $project = Project::create([
            'name' => 'Portal Project',
            'client_id' => $this->client->id,
            'service_type_id' => $service->id,
            'priority' => 'medium',
            'status_id' => $status->id,
            'manager_id' => $admin->id,
            'budget' => 1000,
            'payment_status' => 'unpaid',
            'progress_percentage' => 25,
            'created_by' => $admin->id,
        ]);

        Invoice::create([
            'project_id' => $project->id,
            'client_id' => $this->client->id,
            'invoice_type' => 'initial',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 1000,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 1000,
            'paid_amount' => 250,
            'remaining_amount' => 750,
            'payment_status' => 'partially_paid',
            'created_by' => $admin->id,
        ]);

        Quote::create([
            'client_id' => $this->client->id,
            'subject' => 'Portal Quote',
            'description' => 'Pending quote',
            'subtotal' => 500,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 500,
            'status' => 'sent',
            'valid_until' => now()->addDays(10),
            'created_by' => $admin->id,
        ]);
    }

    public function test_portal_dashboard_shows_kpis_and_lists(): void
    {
        $this->actingAs($this->account, 'client');

        $this->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('Portal Client')
            ->assertSee('1,000.00')
            ->assertSee('250.00')
            ->assertSee('Recent invoices')
            ->assertSee('Recent quotes')
            ->assertSee('Portal Project');
    }

    public function test_portal_translations_exist(): void
    {
        $this->assertNotSame('portal.dashboard', __('portal.dashboard'));
        $this->assertNotSame('portal.outstanding', __('portal.outstanding'));
        $this->assertNotSame('portal.recent_invoices', __('portal.recent_invoices'));
    }
}
