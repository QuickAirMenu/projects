<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountTransaction;
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

class TreasuryFxAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Account $egp;

    protected Client $client;

    protected Project $project;

    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'fx@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $this->egp = Account::create([
            'name' => 'Egypt Bank', 'type' => 'bank', 'currency' => 'EGP',
            'opening_balance' => 0, 'exchange_rate_to_base' => 0.075, 'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-fx',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'FX Client', 'email' => 'fx-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'FX Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 5000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
        $this->invoice = Invoice::create([
            'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(),
            'subtotal' => 1000, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);
    }

    public function test_payment_is_converted_to_account_currency(): void
    {
        $this->actingAs($this->admin)->post(route('payments.store'), [
            'invoice_id' => $this->invoice->id,
            'amount' => 1000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'account_id' => $this->egp->id,
            'exchange_rate' => 13.3,
        ])->assertRedirect(route('invoices.show', $this->invoice));

        $tx = AccountTransaction::where('category', 'client_payment')->firstOrFail();
        $this->assertEquals(13300, (float) $tx->amount);
        $this->assertSame('EGP', $tx->currency);
        $this->assertEquals(997.5, (float) $tx->base_amount);
    }

    public function test_expense_payment_is_converted_to_account_currency(): void
    {
        $this->actingAs($this->admin)->post(route('expenses.store'), [
            'vendor_name' => 'Vendor',
            'category' => 'software',
            'expense_date' => now()->toDateString(),
            'amount' => 1000,
            'tax_amount' => 0,
            'account_id' => $this->egp->id,
            'exchange_rate' => 13.3,
            'paid_at' => now()->toDateString(),
            'project_id' => $this->project->id,
        ])->assertRedirect(route('expenses.index'));

        $tx = AccountTransaction::where('category', 'expense')->firstOrFail();
        $this->assertEquals(13300, (float) $tx->amount);
        $this->assertEquals(997.5, (float) $tx->base_amount);
    }

    public function test_audit_command_passes_on_clean_data(): void
    {
        $this->artisan('treasury:audit')->assertSuccessful();
    }

    public function test_audit_command_detects_base_amount_mismatch(): void
    {
        AccountTransaction::create([
            'account_id' => $this->egp->id, 'transaction_date' => now()->toDateString(),
            'direction' => 'in', 'amount' => 100, 'currency' => 'EGP', 'exchange_rate' => 0.075,
            'base_amount' => 999, 'category' => 'other', 'description' => 'Bad base', 'reconciled_at' => now(),
        ]);

        $this->artisan('treasury:audit')->assertFailed();
    }
}
