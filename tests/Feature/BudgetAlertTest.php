<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\User;
use App\Notifications\ProjectBudgetAlert;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BudgetAlertTest extends TestCase
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
            'name' => 'Manager', 'email' => 'budget@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-ba',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'Budget Client', 'email' => 'budget-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Budget Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_alert_is_sent_when_threshold_reached_and_not_repeated(): void
    {
        Notification::fake();
        $this->expense(950);

        $this->artisan('projects:budget-alerts')->assertSuccessful();

        Notification::assertSentTo($this->admin, ProjectBudgetAlert::class);
        $this->assertNotNull($this->project->fresh()->budget_alert_sent_at);

        $this->artisan('projects:budget-alerts')->assertSuccessful();
        Notification::assertSentTimes(ProjectBudgetAlert::class, 1);
    }

    public function test_no_alert_below_threshold(): void
    {
        Notification::fake();
        $this->expense(400);

        $this->artisan('projects:budget-alerts')->assertSuccessful();

        Notification::assertNothingSent();
        $this->assertNull($this->project->fresh()->budget_alert_sent_at);
    }

    protected function expense(float $total): void
    {
        Expense::create([
            'expense_number' => Expense::nextNumber(), 'vendor_name' => 'Vendor', 'category' => 'software',
            'expense_date' => now()->toDateString(), 'amount' => $total, 'tax_amount' => 0, 'total' => $total,
            'status' => 'approved', 'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'billable' => false, 'created_by' => $this->admin->id,
        ]);
    }
}
