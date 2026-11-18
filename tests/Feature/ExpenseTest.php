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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'expense@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-exp',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'Expense Client', 'email' => 'exp-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Expense Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 5000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_pages_render(): void
    {
        $this->actingAs($this->admin)->get(route('expenses.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('expenses.create'))->assertOk();
    }

    public function test_expense_can_be_created_with_receipt(): void
    {
        $this->actingAs($this->admin)->post(route('expenses.store'), [
            'vendor_name' => 'Adobe',
            'category' => 'software',
            'expense_date' => now()->toDateString(),
            'amount' => 200,
            'tax_amount' => 30,
            'project_id' => $this->project->id,
            'billable' => 1,
            'receipt' => UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('expenses.index'));

        $expense = Expense::firstOrFail();
        $this->assertSame('pending', $expense->status);
        $this->assertEquals(230, (float) $expense->total);
        $this->assertSame($this->client->id, $expense->client_id);
        $this->assertNotNull($expense->receipt_path);
        Storage::disk('private')->assertExists($expense->receipt_path);
    }

    public function test_approve_and_reject_flows(): void
    {
        $a = $this->makeExpense();
        $b = $this->makeExpense(['vendor_name' => 'Figma']);

        $this->actingAs($this->admin)->post(route('expenses.approve', $a))->assertSessionHas('success');
        $a->refresh();
        $this->assertSame('approved', $a->status);
        $this->assertSame($this->admin->id, $a->approved_by);
        $this->assertNotNull($a->approved_at);

        $this->actingAs($this->admin)
            ->post(route('expenses.reject', $b), ['rejection_reason' => 'Duplicate invoice'])
            ->assertSessionHas('success');
        $b->refresh();
        $this->assertSame('rejected', $b->status);
        $this->assertSame('Duplicate invoice', $b->rejection_reason);
    }

    public function test_processed_expense_cannot_be_edited(): void
    {
        $expense = $this->makeExpense(['status' => 'approved']);

        $this->actingAs($this->admin)
            ->put(route('expenses.update', $expense), [
                'vendor_name' => 'Changed',
                'category' => 'other',
                'expense_date' => now()->toDateString(),
                'amount' => 1,
            ])
            ->assertSessionHas('error');

        $this->assertSame('AWS', $expense->fresh()->vendor_name);
    }

    public function test_receipt_can_be_downloaded(): void
    {
        Storage::disk('private')->put('expenses/r.pdf', 'data');
        $expense = $this->makeExpense(['receipt_path' => 'expenses/r.pdf', 'receipt_name' => 'r.pdf']);

        $this->actingAs($this->admin)
            ->get(route('expenses.receipt', $expense))
            ->assertOk()
            ->assertDownload('r.pdf');
    }

    public function test_profitability_includes_approved_expenses(): void
    {
        Invoice::create([
            'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(),
            'subtotal' => 1000, 'tax_percentage' => 15, 'tax_amount' => 150, 'discount_amount' => 0,
            'total' => 1150, 'paid_amount' => 0, 'remaining_amount' => 1150,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);
        $this->makeExpense(['status' => 'approved', 'total' => 300]);
        $this->makeExpense(['status' => 'pending', 'total' => 900]);

        $this->actingAs($this->admin)
            ->get(route('reports.profitability'))
            ->assertOk()
            ->assertSee('300.00')
            ->assertSee('850.00');
    }

    protected function makeExpense(array $overrides = []): Expense
    {
        return Expense::create(array_merge([
            'expense_number' => Expense::nextNumber(),
            'vendor_name' => 'AWS',
            'category' => 'hosting',
            'expense_date' => now()->toDateString(),
            'amount' => 100,
            'tax_amount' => 15,
            'total' => 115,
            'status' => 'pending',
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
            'billable' => true,
            'created_by' => $this->admin->id,
        ], $overrides));
    }
}
