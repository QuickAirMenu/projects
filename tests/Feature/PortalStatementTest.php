<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientPortalAccount;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\InvoiceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PortalStatementTest extends TestCase
{
    use RefreshDatabase;

    protected ClientPortalAccount $account;

    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = User::create([
            'name' => 'Admin', 'email' => 'stmt@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
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
        $client = Client::create([
            'name' => 'Statement Client', 'email' => 'stmt-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $admin->id,
        ]);
        $this->account = ClientPortalAccount::create([
            'client_id' => $client->id, 'password' => 'secret-password',
            'is_active' => true, 'email_verified_at' => now(),
        ]);

        $project = Project::create([
            'name' => 'Statement Project', 'client_id' => $client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $admin->id,
        ]);

        $this->invoice = Invoice::create([
            'project_id' => $project->id, 'client_id' => $client->id, 'invoice_type' => 'initial',
            'invoice_date' => now()->toDateString(), 'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 1000, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000,
            'payment_status' => 'unpaid', 'created_by' => $admin->id,
        ]);

        $this->actingAs($admin);

        app(InvoiceService::class)->recordPayment($this->invoice, [
            'amount' => 300,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'status' => 'approved',
        ]);
    }

    public function test_statement_page_shows_ledger_and_totals(): void
    {
        $this->actingAs($this->account, 'client');

        $this->get(route('portal.statement', [
            'from' => now()->startOfYear()->toDateString(),
            'to' => now()->toDateString(),
        ]))
            ->assertOk()
            ->assertSee($this->invoice->invoice_number)
            ->assertSee('700.00');
    }

    public function test_statement_pdf_is_downloadable(): void
    {
        $this->actingAs($this->account, 'client');

        $response = $this->get(route('portal.statement.pdf', [
            'from' => now()->startOfYear()->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }
}
