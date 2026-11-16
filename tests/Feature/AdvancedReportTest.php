<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdvancedReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'adv@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $client = Client::create([
            'name' => 'Aging Client', 'email' => 'aging@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Aging Project', 'client_id' => $client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 5000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);

        // Current invoice (due in future)
        $this->makeInvoice($client, '2026-12-31', 1000, 0);

        // 45 days overdue invoice
        $this->makeInvoice($client, now()->subDays(45)->toDateString(), 2000, 500);
    }

    public function test_aging_report_buckets_and_totals(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('reports.aging'))
            ->assertOk()
            ->assertSee('1,000.00')   // current
            ->assertSee('1,500.00')   // 45 days overdue remaining
            ->assertSee('2,500.00');  // total outstanding
    }

    public function test_aging_export_csv(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('reports.aging.export'));
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('Invoice #', $response->streamedContent());
    }

    public function test_profitability_summary(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('reports.profitability'))
            ->assertOk()
            ->assertSee('Aging Project')
            ->assertSee('3,000.00')   // invoiced
            ->assertSee('500.00');    // collected
    }

    private function makeInvoice(Client $client, string $due, float $total, float $paid): Invoice
    {
        $invoice = Invoice::create([
            'project_id' => $this->project->id, 'client_id' => $client->id, 'invoice_type' => 'initial',
            'invoice_date' => now()->subDays(60)->toDateString(), 'due_date' => $due,
            'subtotal' => $total, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => $total, 'paid_amount' => $paid, 'remaining_amount' => $total - $paid,
            'payment_status' => $paid > 0 ? 'partially_paid' : 'unpaid', 'created_by' => $this->admin->id,
        ]);

        if ($paid > 0) {
            Payment::create([
                'invoice_id' => $invoice->id, 'payment_number' => 'PAY-ADV-'.$invoice->id,
                'amount' => $paid, 'payment_date' => now()->toDateString(), 'payment_method' => 'cash',
                'status' => 'approved', 'received_by' => $this->admin->id,
            ]);
        }

        return $invoice;
    }
}
