<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\ProjectBudgetAlert;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BudgetConfigTest extends TestCase
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
            'name' => 'Manager', 'email' => 'bcfg@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-bcfg',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $client = Client::create([
            'name' => 'Cfg Client', 'email' => 'cfg-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Cfg Project', 'client_id' => $client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);

        Expense::create([
            'expense_number' => 'EXP-CFG-1', 'vendor_name' => 'Vendor', 'category' => 'software',
            'expense_date' => now()->toDateString(), 'amount' => 600, 'tax_amount' => 0, 'total' => 600,
            'status' => 'approved', 'project_id' => $this->project->id, 'client_id' => $client->id,
            'billable' => false, 'created_by' => $this->admin->id,
        ]);
    }

    public function test_threshold_setting_controls_alerts(): void
    {
        Notification::fake();
        Setting::updateOrCreate(['key' => 'budget_alert_threshold'], ['value' => '50', 'type' => 'string', 'group' => 'finance']);

        $this->artisan('projects:budget-alerts')->assertSuccessful();

        Notification::assertSentTo($this->admin, ProjectBudgetAlert::class);
    }

    public function test_default_threshold_does_not_alert_at_sixty_percent(): void
    {
        Notification::fake();

        $this->artisan('projects:budget-alerts')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_endpoint_accepts_threshold_override(): void
    {
        Notification::fake();
        config(['internal.token' => 'secret-token']);

        $this->post('/internal/budget-alerts', ['token' => 'secret-token', 'threshold' => 50])
            ->assertOk();

        Notification::assertSentTo($this->admin, ProjectBudgetAlert::class);
    }

    public function test_settings_page_shows_budget_fields(): void
    {
        $this->actingAs($this->admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('budget_alert_threshold')
            ->assertSee('budget_alert_interval_days');
    }
}
