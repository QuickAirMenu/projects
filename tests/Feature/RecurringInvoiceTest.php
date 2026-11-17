<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\RecurringInvoice;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RecurringInvoiceTest extends TestCase
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
            'name' => 'Admin', 'email' => 'recurring@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-rec',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'Recurring Client', 'email' => 'recurring-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Recurring Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 5000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_next_date_advances_by_frequency(): void
    {
        $this->assertSame('2026-02-28', RecurringInvoice::nextDate('2026-01-31', 'monthly')->toDateString());
        $this->assertSame('2026-04-30', RecurringInvoice::nextDate('2026-01-31', 'quarterly')->toDateString());
        $this->assertSame('2027-01-31', RecurringInvoice::nextDate('2026-01-31', 'yearly')->toDateString());
    }

    public function test_run_now_generates_invoice_and_advances_schedule(): void
    {
        $recurring = $this->makeRecurring();
        $expectedNext = RecurringInvoice::nextDate($recurring->next_run_date, 'monthly');

        $this->actingAs($this->admin)
            ->post(route('recurring-invoices.run', $recurring))
            ->assertRedirect();

        $invoice = Invoice::with('items')->firstOrFail();
        $this->assertSame($this->client->id, $invoice->client_id);
        $this->assertEquals(200, (float) $invoice->subtotal);
        $this->assertEquals(30, (float) $invoice->tax_amount);
        $this->assertEquals(230, (float) $invoice->total);
        $this->assertEquals(230, (float) $invoice->remaining_amount);
        $this->assertCount(1, $invoice->items);

        $recurring->refresh();
        $this->assertNotNull($recurring->last_run_at);
        $this->assertSame($expectedNext->toDateString(), $recurring->next_run_date->toDateString());
    }

    public function test_command_generates_only_due_active_schedules(): void
    {
        $due = $this->makeRecurring(['name' => 'Due', 'next_run_date' => now()->subDay()->toDateString()]);
        $future = $this->makeRecurring(['name' => 'Future', 'next_run_date' => now()->addMonth()->toDateString()]);
        $inactive = $this->makeRecurring(['name' => 'Inactive', 'next_run_date' => now()->subDay()->toDateString(), 'is_active' => false]);

        $this->actingAs($this->admin);
        $this->artisan('invoices:run-recurring')->assertSuccessful();

        $this->assertSame(1, Invoice::count());
        $this->assertTrue($due->fresh()->next_run_date->isFuture());
        $this->assertSame($future->next_run_date->toDateString(), $future->fresh()->next_run_date->toDateString());
        $this->assertSame($inactive->next_run_date->toDateString(), $inactive->fresh()->next_run_date->toDateString());
    }

    public function test_index_and_create_pages_render(): void
    {
        $this->actingAs($this->admin)
            ->get(route('recurring-invoices.index'))
            ->assertOk()
            ->assertSee(__('finance.recurring_invoices'));

        $this->actingAs($this->admin)
            ->get(route('recurring-invoices.create'))
            ->assertOk();
    }

    public function test_schedule_can_be_created_from_form(): void
    {
        $this->actingAs($this->admin)
            ->post(route('recurring-invoices.store'), [
                'name' => 'Quarterly support',
                'client_id' => $this->client->id,
                'project_id' => $this->project->id,
                'invoice_type' => 'additional',
                'frequency' => 'quarterly',
                'next_run_date' => now()->addMonth()->toDateString(),
                'is_active' => 1,
                'tax_percentage' => 5,
                'items' => [['description' => 'Support', 'quantity' => 1, 'unit_price' => 300]],
            ])
            ->assertRedirect(route('recurring-invoices.index'));

        $this->assertDatabaseHas('recurring_invoices', ['name' => 'Quarterly support', 'frequency' => 'quarterly']);
    }

    protected function makeRecurring(array $overrides = []): RecurringInvoice
    {
        return RecurringInvoice::create(array_merge([
            'client_id' => $this->client->id,
            'project_id' => $this->project->id,
            'name' => 'Monthly retainer',
            'invoice_type' => 'additional',
            'items' => [['description' => 'Retainer', 'quantity' => 2, 'unit_price' => 100]],
            'tax_percentage' => 15,
            'discount_type' => 'fixed',
            'discount_amount' => 0,
            'frequency' => 'monthly',
            'next_run_date' => now()->toDateString(),
            'is_active' => true,
            'created_by' => $this->admin->id,
        ], $overrides));
    }
}
