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

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'dash@example.com',
            'password' => 'password',
            'is_active' => true,
            'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_dashboard_renders_charts_for_financial_roles(): void
    {
        $this->seedBusinessData();
        $this->actingAs($this->admin);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Projects by Status')
            ->assertSee('Invoiced vs Collected')
            ->assertSee('conic-gradient', false)
            ->assertSee('Dash Project', false);
    }

    public function test_dashboard_hides_financial_chart_for_non_financial_roles(): void
    {
        $member = User::create([
            'name' => 'Member',
            'email' => 'member@example.com',
            'password' => 'password',
            'is_active' => true,
            'language' => 'en',
        ]);
        $member->assignRole('team-member');

        $this->actingAs($member);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Projects by Status')
            ->assertDontSee('Invoiced vs Collected');
    }

    public function test_dashboard_renders_without_projects(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Projects by Status');
    }

    private function seedBusinessData(): void
    {
        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ',
            'name_en' => 'In Progress',
            'slug' => 'in-progress',
            'color' => '#3B82F6',
            'sort_order' => 1,
            'is_default' => true,
            'is_active' => true,
        ]);

        $service = ServiceType::create([
            'name_ar' => 'تصميم',
            'name_en' => 'Design',
            'description_ar' => 'تصميم',
            'description_en' => 'Design',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $client = Client::create([
            'name' => 'Dash Client',
            'email' => 'dashclient@example.com',
            'phone' => '0500000000',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $project = Project::create([
            'name' => 'Dash Project',
            'client_id' => $client->id,
            'service_type_id' => $service->id,
            'priority' => 'medium',
            'status_id' => $status->id,
            'manager_id' => $this->admin->id,
            'budget' => 1000,
            'payment_status' => 'unpaid',
            'progress_percentage' => 10,
            'created_by' => $this->admin->id,
        ]);

        Invoice::create([
            'project_id' => $project->id,
            'client_id' => $client->id,
            'invoice_type' => 'initial',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 1000,
            'tax_percentage' => 15,
            'tax_amount' => 150,
            'discount_amount' => 0,
            'total' => 1150,
            'paid_amount' => 500,
            'remaining_amount' => 650,
            'payment_status' => 'partially_paid',
            'created_by' => $this->admin->id,
        ]);
    }
}
