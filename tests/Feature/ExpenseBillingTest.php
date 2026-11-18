<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ExpenseBillingTest extends TestCase
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
            'name' => 'Admin', 'email' => 'ebill@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-ebill',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'Bill Client', 'email' => 'bill-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Bill Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 5000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_preview_and_bill_approved_unbilled_expenses(): void
    {
        $a = $this->expense(['amount' => 100]);
        $b = $this->expense(['amount' => 250, 'vendor_name' => 'Domain Co']);
        $this->expense(['status' => 'pending']);
        $this->expense(['billable' => false]);

        $this->actingAs($this->admin)
            ->get(route('expenses.bill.preview', ['project_id' => $this->project->id]))
            ->assertOk()
            ->assertSee('Hosting Co')
            ->assertSee('Domain Co');

        $this->actingAs($this->admin)
            ->post(route('expenses.bill.store'), [
                'project_id' => $this->project->id,
                'from' => now()->subDays(2)->toDateString(),
                'to' => now()->toDateString(),
                'tax_percentage' => 15,
            ])
            ->assertRedirect();

        $invoice = Invoice::with('items')->firstOrFail();
        $this->assertSame($this->client->id, $invoice->client_id);
        $this->assertEquals(350, (float) $invoice->subtotal);
        $this->assertEquals(52.5, (float) $invoice->tax_amount);
        $this->assertEquals(402.5, (float) $invoice->total);
        $this->assertCount(2, $invoice->items);

        $this->assertSame($invoice->id, $a->fresh()->invoiced_invoice_id);
        $this->assertSame($invoice->id, $b->fresh()->invoiced_invoice_id);
    }

    public function test_cannot_bill_same_expenses_twice(): void
    {
        $this->expense();

        $payload = [
            'project_id' => $this->project->id,
            'from' => now()->subDays(2)->toDateString(),
            'to' => now()->toDateString(),
            'tax_percentage' => 15,
        ];

        $this->actingAs($this->admin)->post(route('expenses.bill.store'), $payload)->assertRedirect();
        $this->actingAs($this->admin)->post(route('expenses.bill.store'), $payload)->assertSessionHasErrors('from');

        $this->assertSame(1, Invoice::count());
    }

    public function test_bill_page_renders(): void
    {
        $this->actingAs($this->admin)->get(route('expenses.bill.preview'))->assertOk();
    }

    protected function expense(array $overrides = []): Expense
    {
        return Expense::create(array_merge([
            'expense_number' => Expense::nextNumber(),
            'vendor_name' => 'Hosting Co',
            'category' => 'hosting',
            'expense_date' => now()->toDateString(),
            'amount' => 100,
            'tax_amount' => 15,
            'total' => 115,
            'status' => 'approved',
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
            'billable' => true,
            'created_by' => $this->admin->id,
        ], $overrides));
    }
}
