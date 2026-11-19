<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class HelpTooltipTest extends TestCase
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
            'name' => 'Admin', 'email' => 'help@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-help',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'Help Client', 'email' => 'help-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Help Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'contract_value' => 2000,
            'payment_status' => 'unpaid', 'progress_percentage' => 0, 'created_by' => $this->admin->id,
        ]);
    }

    public function test_project_forms_render_working_tooltip_markup(): void
    {
        foreach ([route('projects.create'), route('projects.edit', $this->project)] as $url) {
            $this->actingAs($this->admin)
                ->get($url)
                ->assertOk()
                ->assertSee('ip-tip-wrap', false)
                ->assertSee('role="tooltip"', false);
        }
    }

    public function test_other_forms_use_tooltip_component(): void
    {
        foreach ([route('tasks.create'), route('users.create'), route('quotes.create')] as $url) {
            $this->actingAs($this->admin)
                ->get($url)
                ->assertOk()
                ->assertSee('ip-tip-wrap', false);
        }
    }
}
