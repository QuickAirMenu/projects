<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TimeEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TaskCostTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected Project $project;

    protected TaskStatus $taskStatus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Dev', 'email' => 'dev@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-tc',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'TC Client', 'email' => 'tc-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'TC Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 5000, 'contract_value' => 5000,
            'payment_status' => 'unpaid', 'progress_percentage' => 0, 'created_by' => $this->admin->id,
        ]);
        $this->taskStatus = TaskStatus::create([
            'name_ar' => 'جديدة', 'name_en' => 'Todo', 'slug' => 'todo-tc',
            'color' => '#6B7280', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
    }

    public function test_task_cost_can_be_set_and_paid(): void
    {
        $this->actingAs($this->admin)->post(route('tasks.store'), [
            'project_id' => $this->project->id,
            'title' => 'Profile website design',
            'status_id' => $this->taskStatus->id,
            'priority' => 'medium',
            'assigned_to' => $this->admin->id,
            'estimated_hours' => 5,
            'cost_amount' => 3000,
        ])->assertRedirect();

        $task = Task::firstOrFail();
        $this->assertEquals(3000, (float) $task->cost_amount);

        $this->actingAs($this->admin)
            ->get(route('tasks.show', $task))
            ->assertOk()
            ->assertSee('3,000.00')
            ->assertSee(__('task.cost_unpaid'));

        $this->actingAs($this->admin)->post(route('tasks.cost.pay', $task))->assertRedirect();
        $this->assertNotNull($task->fresh()->cost_paid_at);

        $this->actingAs($this->admin)->post(route('tasks.cost.unpay', $task))->assertRedirect();
        $this->assertNull($task->fresh()->cost_paid_at);
    }

    public function test_profitability_uses_task_cost_instead_of_timesheets(): void
    {
        $this->task(['cost_amount' => 3000]);

        TimeEntry::create([
            'project_id' => $this->project->id, 'user_id' => $this->admin->id,
            'work_date' => now()->toDateString(), 'hours' => 10, 'description' => 'Work',
            'billable' => true, 'hourly_rate' => 999, 'created_by' => $this->admin->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-TC-1', 'vendor_name' => 'Vendor', 'category' => 'software',
            'expense_date' => now()->toDateString(), 'amount' => 500, 'tax_amount' => 0, 'total' => 500,
            'status' => 'approved', 'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'billable' => false, 'created_by' => $this->admin->id,
        ]);
        Invoice::create([
            'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(),
            'subtotal' => 5000, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 5000, 'paid_amount' => 0, 'remaining_amount' => 5000,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.profitability'))
            ->assertOk()
            ->assertSee('3,000.00')   // labor = task cost
            ->assertSee('3,500.00')   // total cost = 3000 + 500
            ->assertSee('1,500.00')   // net
            ->assertSee('30%');       // margin
    }

    public function test_team_payouts_report_lists_unpaid_and_paid_totals(): void
    {
        $unpaid = $this->task(['title' => 'Unpaid task', 'cost_amount' => 3000]);
        $paid = $this->task(['title' => 'Paid task', 'cost_amount' => 1000, 'cost_paid_at' => now()]);

        $this->actingAs($this->admin)
            ->get(route('reports.team-payouts'))
            ->assertOk()
            ->assertSee('Unpaid task')
            ->assertSee('3,000.00')
            ->assertDontSee('Paid task');

        $this->actingAs($this->admin)
            ->get(route('reports.team-payouts', ['status' => 'all']))
            ->assertOk()
            ->assertSee('Paid task');

        $this->actingAs($this->admin)->post(route('tasks.cost.pay', $unpaid))->assertRedirect();
        $this->assertNotNull($unpaid->fresh()->cost_paid_at);
    }

    protected function task(array $overrides = []): Task
    {
        return Task::create(array_merge([
            'project_id' => $this->project->id, 'title' => 'Design landing page',
            'status_id' => $this->taskStatus->id, 'priority' => 'medium',
            'assigned_to' => $this->admin->id, 'actual_hours' => 0, 'progress_percentage' => 0,
            'cost_amount' => 3000, 'created_by' => $this->admin->id,
        ], $overrides));
    }
}
