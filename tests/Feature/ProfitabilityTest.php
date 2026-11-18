<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\TimeEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProfitabilityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'pl@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-pl',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'P&L Client', 'email' => 'pl-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'P&L Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 2000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);

        $invoice = Invoice::create([
            'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(),
            'subtotal' => 1000, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 1000, 'paid_amount' => 500, 'remaining_amount' => 500,
            'payment_status' => 'partially_paid', 'created_by' => $this->admin->id,
        ]);
        Payment::create([
            'invoice_id' => $invoice->id, 'payment_number' => 'PAY-PL-1',
            'amount' => 500, 'payment_date' => now()->toDateString(), 'payment_method' => 'cash',
            'status' => 'approved', 'received_by' => $this->admin->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-PL-1', 'vendor_name' => 'Vendor', 'category' => 'software',
            'expense_date' => now()->toDateString(), 'amount' => 180, 'tax_amount' => 20, 'total' => 200,
            'status' => 'approved', 'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'billable' => false, 'created_by' => $this->admin->id,
        ]);
        TimeEntry::create([
            'project_id' => $this->project->id, 'user_id' => $this->admin->id,
            'work_date' => now()->toDateString(), 'hours' => 2, 'description' => 'Development',
            'billable' => true, 'hourly_rate' => 100, 'created_by' => $this->admin->id,
        ]);
    }

    public function test_profitability_includes_labor_cost_and_margin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.profitability'))
            ->assertOk()
            ->assertSee('200.00')   // expenses
            ->assertSee('400.00')   // total cost (200 expenses + 200 labor)
            ->assertSee('600.00')   // net (1000 - 400)
            ->assertSee('60%');     // margin
    }

    public function test_profitability_csv_export_contains_labor_and_net(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.profitability.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('P&L Project', $content);
        $this->assertStringContainsString('200.00', $content);
        $this->assertStringContainsString('600.00', $content);
    }
}
