<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\TimeEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WipReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected Project $project;

    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'wip@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-wip',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'WIP Client', 'email' => 'wip-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'WIP Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 5000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
        $this->invoice = Invoice::create([
            'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(),
            'subtotal' => 100, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 100, 'paid_amount' => 0, 'remaining_amount' => 100,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);
    }

    public function test_wip_report_aggregates_only_unbilled_billable_items(): void
    {
        $this->timeEntry(2, 100);                    // 200 unbilled
        $this->timeEntry(1, 100, $this->invoice->id); // invoiced -> excluded
        $this->timeEntry(3, 100, null, false);        // non-billable -> excluded
        $this->expense(50);                           // 50 unbilled
        $this->expense(25, false);                    // non-billable -> excluded
        $this->expense(75, true, 'pending');          // pending -> excluded

        $this->actingAs($this->admin)
            ->get(route('reports.wip'))
            ->assertOk()
            ->assertSee('WIP Project')
            ->assertSee('200.00')
            ->assertSee('50.00')
            ->assertSee('250.00');

        $csv = $this->actingAs($this->admin)->get(route('reports.wip.export'))->streamedContent();
        $this->assertStringContainsString('Unbilled hours', $csv);
        $this->assertStringContainsString('WIP Project', $csv);
        $this->assertStringContainsString('200.00', $csv);
        $this->assertStringContainsString('250.00', $csv);
    }

    public function test_wip_requires_reports_permission(): void
    {
        $user = User::create([
            'name' => 'Member', 'email' => 'wip-member@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $user->assignRole('team-member');

        $this->actingAs($user)->get(route('reports.wip'))->assertForbidden();
    }

    public function test_empty_state_when_nothing_unbilled(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.wip'))
            ->assertOk()
            ->assertSee(__('reports.wip_empty'));
    }

    protected function timeEntry(float $hours, float $rate, ?int $invoiceId = null, bool $billable = true): void
    {
        TimeEntry::create([
            'project_id' => $this->project->id, 'user_id' => $this->admin->id,
            'work_date' => now()->toDateString(), 'hours' => $hours, 'description' => 'Work',
            'billable' => $billable, 'hourly_rate' => $rate,
            'invoiced_invoice_id' => $invoiceId, 'created_by' => $this->admin->id,
        ]);
    }

    protected function expense(float $amount, bool $billable = true, string $status = 'approved', ?int $invoiceId = null): void
    {
        Expense::create([
            'expense_number' => Expense::nextNumber(), 'vendor_name' => 'Vendor', 'category' => 'software',
            'expense_date' => now()->toDateString(), 'amount' => $amount, 'tax_amount' => 0, 'total' => $amount,
            'status' => $status, 'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'billable' => $billable, 'invoiced_invoice_id' => $invoiceId, 'created_by' => $this->admin->id,
        ]);
    }
}
