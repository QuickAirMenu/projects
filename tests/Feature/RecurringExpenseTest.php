<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\RecurringExpense;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\RecurringExpenseService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RecurringExpenseTest extends TestCase
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
            'name' => 'Admin', 'email' => 'rexp@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-rexp',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'RExp Client', 'email' => 'rexp-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'RExp Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 5000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_next_date_advances_by_frequency(): void
    {
        $this->assertSame('2026-02-28', RecurringExpense::nextDate('2026-01-31', 'monthly')->toDateString());
        $this->assertSame('2026-04-30', RecurringExpense::nextDate('2026-01-31', 'quarterly')->toDateString());
        $this->assertSame('2027-01-31', RecurringExpense::nextDate('2026-01-31', 'yearly')->toDateString());
    }

    public function test_run_now_generates_pending_expense_and_advances_schedule(): void
    {
        $recurring = $this->recurring();
        $expectedNext = RecurringExpense::nextDate($recurring->next_run_date, 'monthly');

        $this->actingAs($this->admin)
            ->post(route('recurring-expenses.run', $recurring))
            ->assertRedirect(route('expenses.index'));

        $expense = Expense::firstOrFail();
        $this->assertSame('pending', $expense->status);
        $this->assertEquals(115, (float) $expense->total);
        $this->assertSame($recurring->id, $expense->recurring_expense_id);
        $this->assertSame($this->client->id, $expense->client_id);

        $recurring->refresh();
        $this->assertNotNull($recurring->last_run_at);
        $this->assertSame($expectedNext->toDateString(), $recurring->next_run_date->toDateString());
    }

    public function test_command_processes_only_due_active_schedules(): void
    {
        $due = $this->recurring(['next_run_date' => now()->subDay()->toDateString()]);
        $future = $this->recurring(['next_run_date' => now()->addMonth()->toDateString()]);
        $inactive = $this->recurring(['next_run_date' => now()->subDay()->toDateString(), 'is_active' => false]);

        $this->artisan('expenses:run-recurring')->assertSuccessful();

        $this->assertSame(1, Expense::count());
        $this->assertTrue($due->fresh()->next_run_date->isFuture());
        $this->assertSame($future->next_run_date->toDateString(), $future->fresh()->next_run_date->toDateString());
        $this->assertSame($inactive->next_run_date->toDateString(), $inactive->fresh()->next_run_date->toDateString());
    }

    public function test_service_skips_future_schedule_without_force(): void
    {
        $recurring = $this->recurring(['next_run_date' => now()->addDays(10)->toDateString()]);

        $result = app(RecurringExpenseService::class)->generate($recurring, false);

        $this->assertNull($result);
        $this->assertSame(0, Expense::count());
    }

    public function test_pages_render_and_schedule_can_be_created(): void
    {
        $this->actingAs($this->admin)->get(route('recurring-expenses.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('recurring-expenses.create'))->assertOk();

        $this->actingAs($this->admin)->post(route('recurring-expenses.store'), [
            'vendor_name' => 'Adobe',
            'category' => 'software',
            'amount' => 200,
            'tax_amount' => 30,
            'frequency' => 'yearly',
            'next_run_date' => now()->addMonth()->toDateString(),
            'is_active' => 1,
        ])->assertRedirect(route('recurring-expenses.index'));

        $this->assertDatabaseHas('recurring_expenses', ['vendor_name' => 'Adobe', 'frequency' => 'yearly']);
    }

    protected function recurring(array $overrides = []): RecurringExpense
    {
        return RecurringExpense::create(array_merge([
            'vendor_name' => 'Hosting Co',
            'category' => 'hosting',
            'amount' => 100,
            'tax_amount' => 15,
            'frequency' => 'monthly',
            'next_run_date' => now()->toDateString(),
            'is_active' => true,
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
            'billable' => true,
            'created_by' => $this->admin->id,
        ], $overrides));
    }
}
