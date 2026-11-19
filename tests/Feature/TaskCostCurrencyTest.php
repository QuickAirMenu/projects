<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TaskCostCurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Project $project;

    protected TaskStatus $taskStatus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'egp@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-egp',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $client = Client::create([
            'name' => 'EGP Client', 'email' => 'egp-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'EGP Project', 'client_id' => $client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 5000, 'contract_value' => 5000,
            'payment_status' => 'unpaid', 'progress_percentage' => 0, 'created_by' => $this->admin->id,
        ]);
        $this->taskStatus = TaskStatus::create([
            'name_ar' => 'جديدة', 'name_en' => 'Todo', 'slug' => 'todo-egp',
            'color' => '#6B7280', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);

        Invoice::create([
            'project_id' => $this->project->id, 'client_id' => $client->id,
            'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(),
            'subtotal' => 5000, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 5000, 'paid_amount' => 0, 'remaining_amount' => 5000,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);
    }

    public function test_task_cost_in_egp_is_converted_to_base_in_profitability(): void
    {
        Task::create([
            'project_id' => $this->project->id, 'title' => 'Design profile site',
            'status_id' => $this->taskStatus->id, 'priority' => 'medium',
            'assigned_to' => $this->admin->id, 'actual_hours' => 0, 'progress_percentage' => 0,
            'cost_amount' => 3000, 'cost_currency' => 'EGP', 'cost_exchange_rate' => 0.075,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.profitability'))
            ->assertOk()
            ->assertSee('225.00')   // labor converted to base
            ->assertSee('4,775.00'); // net = 5000 - 225
    }

    public function test_task_form_accepts_currency_and_rate(): void
    {
        $this->actingAs($this->admin)->post(route('tasks.store'), [
            'project_id' => $this->project->id,
            'title' => 'EGP task',
            'status_id' => $this->taskStatus->id,
            'priority' => 'medium',
            'assigned_to' => $this->admin->id,
            'cost_amount' => 3000,
            'cost_currency' => 'EGP',
            'cost_exchange_rate' => 0.075,
        ])->assertRedirect();

        $task = Task::firstOrFail();
        $this->assertSame('EGP', $task->cost_currency);
        $this->assertEquals(0.075, (float) $task->cost_exchange_rate);

        $this->actingAs($this->admin)
            ->get(route('tasks.show', $task))
            ->assertOk()
            ->assertSee('3,000.00')
            ->assertSee('EGP');
    }
}
