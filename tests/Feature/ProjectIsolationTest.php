<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Quote;
use App\Models\ServiceType;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TimeEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProjectIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $manager;

    protected User $otherManager;

    protected Client $client1;

    protected Client $client2;

    protected Project $project1;

    protected Project $project2;

    protected Task $task1;

    protected Task $task2;

    protected Invoice $invoice1;

    protected Invoice $invoice2;

    protected Quote $quote1;

    protected Quote $quote2;

    protected Payment $payment1;

    protected Payment $payment2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create(['name' => 'Admin', 'email' => 'iso-admin@example.com', 'password' => 'password', 'is_active' => true, 'language' => 'en']);
        $this->admin->assignRole('super-admin');
        $this->manager = User::create(['name' => 'Manager One', 'email' => 'mgr1@example.com', 'password' => 'password', 'is_active' => true, 'language' => 'en']);
        $this->manager->assignRole('project-manager');
        $this->manager->givePermissionTo('view-expenses');
        $this->otherManager = User::create(['name' => 'Manager Two', 'email' => 'mgr2@example.com', 'password' => 'password', 'is_active' => true, 'language' => 'en']);
        $this->otherManager->assignRole('project-manager');

        $status = ProjectStatus::create(['name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-iso', 'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true]);
        $service = ServiceType::create(['name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x', 'is_active' => true, 'sort_order' => 1]);
        $taskStatus = TaskStatus::create(['name_ar' => 'جديدة', 'name_en' => 'Todo', 'slug' => 'todo-iso', 'color' => '#6B7280', 'sort_order' => 1, 'is_default' => true, 'is_active' => true]);

        $this->client1 = Client::create(['name' => 'Client One', 'email' => 'c1@example.com', 'phone' => '0500000000', 'status' => 'active', 'created_by' => $this->admin->id]);
        $this->client2 = Client::create(['name' => 'Client Two', 'email' => 'c2@example.com', 'phone' => '0500000001', 'status' => 'active', 'created_by' => $this->admin->id]);

        $this->project1 = Project::create(['name' => 'Project One', 'client_id' => $this->client1->id, 'service_type_id' => $service->id, 'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->manager->id, 'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0, 'created_by' => $this->admin->id]);
        $this->project2 = Project::create(['name' => 'Project Two', 'client_id' => $this->client2->id, 'service_type_id' => $service->id, 'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->otherManager->id, 'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0, 'created_by' => $this->admin->id]);

        $this->task1 = Task::create(['project_id' => $this->project1->id, 'title' => 'Task One', 'status_id' => $taskStatus->id, 'priority' => 'medium', 'assigned_to' => $this->manager->id, 'actual_hours' => 0, 'progress_percentage' => 0, 'created_by' => $this->admin->id]);
        $this->task2 = Task::create(['project_id' => $this->project2->id, 'title' => 'Task Two', 'status_id' => $taskStatus->id, 'priority' => 'medium', 'assigned_to' => $this->otherManager->id, 'actual_hours' => 0, 'progress_percentage' => 0, 'created_by' => $this->admin->id]);

        $this->invoice1 = Invoice::create(['project_id' => $this->project1->id, 'client_id' => $this->client1->id, 'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(), 'subtotal' => 100, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0, 'total' => 100, 'paid_amount' => 0, 'remaining_amount' => 100, 'payment_status' => 'unpaid', 'created_by' => $this->admin->id]);
        $this->invoice2 = Invoice::create(['project_id' => $this->project2->id, 'client_id' => $this->client2->id, 'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(), 'subtotal' => 200, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0, 'total' => 200, 'paid_amount' => 0, 'remaining_amount' => 200, 'payment_status' => 'unpaid', 'created_by' => $this->admin->id]);

        $this->quote1 = Quote::create(['quote_number' => 'QUO-ISO-1', 'public_token' => Str::random(32), 'client_id' => $this->client1->id, 'subject' => 'Quote One', 'subtotal' => 100, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0, 'total' => 100, 'status' => 'draft', 'valid_until' => now()->addDays(5)->toDateString(), 'created_by' => $this->admin->id]);
        $this->quote2 = Quote::create(['quote_number' => 'QUO-ISO-2', 'public_token' => Str::random(32), 'client_id' => $this->client2->id, 'subject' => 'Quote Two', 'subtotal' => 200, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0, 'total' => 200, 'status' => 'draft', 'valid_until' => now()->addDays(5)->toDateString(), 'created_by' => $this->admin->id]);

        $this->payment1 = Payment::create(['invoice_id' => $this->invoice1->id, 'payment_number' => 'PAY-ISO-1', 'amount' => 50, 'payment_date' => now()->toDateString(), 'payment_method' => 'cash', 'status' => 'approved', 'received_by' => $this->admin->id]);
        $this->payment2 = Payment::create(['invoice_id' => $this->invoice2->id, 'payment_number' => 'PAY-ISO-2', 'amount' => 50, 'payment_date' => now()->toDateString(), 'payment_method' => 'cash', 'status' => 'approved', 'received_by' => $this->admin->id]);

        Expense::create(['expense_number' => 'EXP-ISO-1', 'vendor_name' => 'V1', 'category' => 'software', 'expense_date' => now()->toDateString(), 'amount' => 10, 'tax_amount' => 0, 'total' => 10, 'status' => 'approved', 'project_id' => $this->project1->id, 'created_by' => $this->admin->id]);
        Expense::create(['expense_number' => 'EXP-ISO-2', 'vendor_name' => 'V2', 'category' => 'software', 'expense_date' => now()->toDateString(), 'amount' => 20, 'tax_amount' => 0, 'total' => 20, 'status' => 'approved', 'project_id' => $this->project2->id, 'created_by' => $this->admin->id]);

        TimeEntry::create(['project_id' => $this->project1->id, 'user_id' => $this->manager->id, 'work_date' => now()->toDateString(), 'hours' => 2, 'description' => 'iso one', 'billable' => true, 'hourly_rate' => 10, 'created_by' => $this->admin->id]);
        TimeEntry::create(['project_id' => $this->project2->id, 'user_id' => $this->otherManager->id, 'work_date' => now()->toDateString(), 'hours' => 2, 'description' => 'iso two', 'billable' => true, 'hourly_rate' => 10, 'created_by' => $this->admin->id]);
    }

    public function test_scoped_manager_sees_only_assigned_project_data(): void
    {
        $this->actingAs($this->manager);

        $this->get(route('projects.index'))->assertOk()->assertSee('Project One')->assertDontSee('Project Two');
        $this->get(route('projects.show', $this->project2))->assertNotFound();
        $this->get(route('tasks.index'))->assertOk()->assertSee('Task One')->assertDontSee('Task Two');
        $this->get(route('tasks.show', $this->task2))->assertNotFound();
        $this->get(route('invoices.index'))->assertOk()->assertSee($this->invoice1->invoice_number)->assertDontSee($this->invoice2->invoice_number);
        $this->get(route('invoices.show', $this->invoice2))->assertNotFound();
        $this->get(route('payments.index'))->assertOk()->assertSee('PAY-ISO-1')->assertDontSee('PAY-ISO-2');
        $this->get(route('quotes.index'))->assertOk()->assertSee('QUO-ISO-1')->assertDontSee('QUO-ISO-2');
        $this->get(route('quotes.show', $this->quote2))->assertNotFound();
        $this->get(route('clients.index'))->assertOk()->assertSee('Client One')->assertDontSee('Client Two');
        $this->get(route('clients.show', $this->client2))->assertNotFound();
        $this->get(route('expenses.index'))->assertOk()->assertSee('EXP-ISO-1')->assertDontSee('EXP-ISO-2');
        $this->get(route('time-entries.index'))->assertOk()->assertSee('iso one')->assertDontSee('iso two');

        $this->get(route('reports.aging'))->assertOk()->assertSee($this->invoice1->invoice_number)->assertDontSee($this->invoice2->invoice_number);
        $this->get(route('reports.wip'))->assertOk()->assertSee('Project One')->assertDontSee('Project Two');
    }

    public function test_api_is_scoped_too(): void
    {
        Sanctum::actingAs($this->manager);

        $this->getJson('/api/v1/projects')->assertOk()->assertJsonFragment(['name' => 'Project One'])->assertJsonMissing(['name' => 'Project Two']);
        $this->getJson('/api/v1/invoices')->assertOk()->assertJsonFragment(['number' => $this->invoice1->invoice_number])->assertJsonMissing(['number' => $this->invoice2->invoice_number]);
    }

    public function test_admin_finance_bypass_scope(): void
    {
        $this->actingAs($this->admin)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Project One')
            ->assertSee('Project Two');
    }
}
