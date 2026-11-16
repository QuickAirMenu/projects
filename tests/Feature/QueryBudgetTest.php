<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class QueryBudgetTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected ProjectStatus $projectStatus;

    protected TaskStatus $taskStatus;

    protected ServiceType $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'perf@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $this->projectStatus = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $this->taskStatus = TaskStatus::create([
            'name_ar' => 'قيد العمل', 'name_en' => 'In Progress', 'slug' => 'in-progress',
            'color' => '#8B5CF6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $this->service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);

        // Seed 6 clients with projects/invoices/payments/tasks to expose N+1 patterns.
        for ($i = 1; $i <= 6; $i++) {
            $client = Client::create([
                'name' => "Client $i", 'email' => "client$i@example.com", 'phone' => '0500000000',
                'status' => 'active', 'created_by' => $this->admin->id,
            ]);

            $project = Project::create([
                'name' => "Project $i", 'client_id' => $client->id, 'service_type_id' => $this->service->id,
                'priority' => 'medium', 'status_id' => $this->projectStatus->id, 'manager_id' => $this->admin->id,
                'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 10,
                'created_by' => $this->admin->id,
            ]);

            $invoice = Invoice::create([
                'project_id' => $project->id, 'client_id' => $client->id, 'invoice_type' => 'initial',
                'invoice_date' => now()->toDateString(), 'due_date' => now()->addDays(30)->toDateString(),
                'subtotal' => 1000, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
                'total' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000,
                'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
            ]);

            Payment::create([
                'invoice_id' => $invoice->id, 'payment_number' => 'PAY-T-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'amount' => 100, 'payment_date' => now()->toDateString(), 'payment_method' => 'cash',
                'status' => 'approved', 'received_by' => $this->admin->id,
            ]);

            Task::create([
                'project_id' => $project->id, 'title' => "Task $i", 'description' => 'x',
                'status_id' => $this->taskStatus->id, 'priority' => 'high', 'assigned_to' => $this->admin->id,
                'progress_percentage' => 0, 'created_by' => $this->admin->id,
            ]);
        }
    }

    public function test_clients_index_query_budget(): void
    {
        $this->assertQueryBudget('clients.index', 25);
    }

    public function test_projects_index_query_budget(): void
    {
        $this->assertQueryBudget('projects.index', 30);
    }

    public function test_invoices_index_query_budget(): void
    {
        $this->assertQueryBudget('invoices.index', 26);
    }

    public function test_payments_index_query_budget(): void
    {
        $this->assertQueryBudget('payments.index', 28);
    }

    public function test_tasks_index_query_budget(): void
    {
        $this->assertQueryBudget('tasks.index', 30);
    }

    public function test_finance_report_query_budget(): void
    {
        $this->assertQueryBudget('reports.finance', 34);
    }

    private function assertQueryBudget(string $routeName, int $maxQueries): void
    {
        $this->actingAs($this->admin);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get(route($routeName))->assertOk();

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            $maxQueries,
            $count,
            "Query budget exceeded for {$routeName}: {$count} queries (max {$maxQueries})"
        );
    }
}
