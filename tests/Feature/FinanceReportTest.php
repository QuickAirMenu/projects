<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FinanceReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'reports@example.com',
            'password' => 'password',
            'is_active' => true,
            'language' => 'en',
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
        $this->client = Client::create([
            'name' => 'Acme Co', 'email' => 'acme@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $project = Project::create([
            'name' => 'Acme Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);

        $this->makeInvoice($project, '2026-01-15', 1000, 400);
        $this->makeInvoice($project, '2026-06-10', 2000, 2000);
    }

    public function test_finance_report_totals_respect_date_filters(): void
    {
        $this->actingAs($this->admin);

        $all = $this->get(route('reports.finance', ['from' => '2026-01-01', 'to' => '2026-12-31']));
        $all->assertOk();
        $all->assertSee('3,450.00');
        $all->assertSee('Acme Co');

        $onlyFirstHalf = $this->get(route('reports.finance', ['from' => '2026-01-01', 'to' => '2026-03-31']));
        $onlyFirstHalf->assertOk();
        $onlyFirstHalf->assertSee('1,150.00');
        $onlyFirstHalf->assertDontSee('2,300.00');
    }

    public function test_finance_report_shows_top_clients(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('reports.finance'))
            ->assertOk()
            ->assertSee('Top Clients')
            ->assertSee('Acme Co');
    }

    public function test_finance_report_exports_csv(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('reports.finance.export', ['from' => '2026-01-01', 'to' => '2026-12-31']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Invoice #', $content);
        $this->assertStringContainsString('Acme Co', $content);
        $this->assertStringContainsString('2300.00', $content);
    }

    private function makeInvoice(Project $project, string $date, float $total, float $paid): Invoice
    {
        return Invoice::create([
            'project_id' => $project->id,
            'client_id' => $this->client->id,
            'invoice_type' => 'initial',
            'invoice_date' => $date,
            'due_date' => $date,
            'subtotal' => $total,
            'tax_percentage' => 15,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => $total,
            'paid_amount' => $paid,
            'remaining_amount' => $total - $paid,
            'payment_status' => $paid >= $total ? 'paid' : ($paid > 0 ? 'partially_paid' : 'unpaid'),
            'created_by' => $this->admin->id,
        ]);
    }
}
