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

class ContractValueTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected ProjectStatus $status;

    protected ServiceType $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'contract@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $this->status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-cv',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $this->service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'Contract Client', 'email' => 'contract-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
    }

    public function test_project_can_store_contract_value_and_cost_budget_separately(): void
    {
        $this->actingAs($this->admin)->post(route('projects.store'), [
            'name' => 'Contract Project',
            'client_id' => $this->client->id,
            'service_type_id' => $this->service->id,
            'status_id' => $this->status->id,
            'manager_id' => $this->admin->id,
            'priority' => 'medium',
            'contract_value' => 50000,
            'budget' => 30000,
        ])->assertRedirect();

        $project = Project::firstOrFail();
        $this->assertEquals(50000, (float) $project->contract_value);
        $this->assertEquals(30000, (float) $project->budget);

        $this->actingAs($this->admin)
            ->get(route('projects.edit', $project))
            ->assertOk()
            ->assertSee('name="contract_value"', false)
            ->assertSee('name="budget"', false);

        $this->actingAs($this->admin)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('50,000.00');
    }

    public function test_profitability_shows_contract_and_remaining_to_invoice(): void
    {
        $project = Project::create([
            'name' => 'Contract Report', 'client_id' => $this->client->id, 'service_type_id' => $this->service->id,
            'priority' => 'medium', 'status_id' => $this->status->id, 'manager_id' => $this->admin->id,
            'budget' => 30000, 'contract_value' => 50000,
            'payment_status' => 'unpaid', 'progress_percentage' => 0, 'created_by' => $this->admin->id,
        ]);

        Invoice::create([
            'project_id' => $project->id, 'client_id' => $this->client->id,
            'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(),
            'subtotal' => 20000, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 20000, 'paid_amount' => 0, 'remaining_amount' => 20000,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.profitability'))
            ->assertOk()
            ->assertSee('50,000.00')
            ->assertSee('30,000.00');

        $csv = $this->actingAs($this->admin)->get(route('reports.profitability.export'))->streamedContent();
        $this->assertStringContainsString('Contract', $csv);
        $this->assertStringContainsString('50000.00', $csv);
        $this->assertStringContainsString('30000.00', $csv);
    }
}
