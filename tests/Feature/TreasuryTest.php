<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TreasuryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Account $sar;

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
            'name' => 'Admin', 'email' => 'treasury@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $this->sar = Account::create([
            'name' => 'Saudi Bank', 'type' => 'bank', 'currency' => 'SAR',
            'opening_balance' => 10000, 'exchange_rate_to_base' => 1, 'is_active' => true,
            'created_by' => $this->admin->id,
        ]);
        $this->egp = Account::create([
            'name' => 'Egypt Bank', 'type' => 'bank', 'currency' => 'EGP',
            'opening_balance' => 50000, 'exchange_rate_to_base' => 0.075, 'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-tr',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'Treasury Client', 'email' => 'tr-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Treasury Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 5000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
        $this->invoice = Invoice::create([
            'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(),
            'subtotal' => 2000, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 2000, 'paid_amount' => 0, 'remaining_amount' => 2000,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);
    }

    public function test_account_can_be_created_and_balances_render(): void
    {
        $this->actingAs($this->admin)->post(route('accounts.store'), [
            'name' => 'Cash EGP', 'type' => 'cash', 'currency' => 'egp',
            'opening_balance' => 1000, 'exchange_rate_to_base' => 0.075, 'is_active' => 1,
        ])->assertRedirect(route('accounts.index'));

        $account = Account::where('name', 'Cash EGP')->firstOrFail();
        $this->assertSame('EGP', $account->currency);
        $this->assertEquals(75, $account->baseBalance());

        $this->actingAs($this->admin)
            ->get(route('accounts.index'))
            ->assertOk()
            ->assertSee('Cash EGP');

        $this->actingAs($this->admin)
            ->get(route('accounts.show', $this->egp))
            ->assertOk()
            ->assertSee('50,000.00');
    }

    public function test_manual_transactions_and_transfer_update_balances(): void
    {
        $this->actingAs($this->admin)->post(route('accounts.transactions.store', $this->egp), [
            'direction' => 'in', 'amount' => 2000, 'transaction_date' => now()->toDateString(),
            'category' => 'adjustment', 'description' => 'Deposit', 'exchange_rate' => 0.075,
        ])->assertRedirect();

        $this->actingAs($this->admin)->post(route('accounts.transfer.store'), [
            'from_account_id' => $this->sar->id,
            'to_account_id' => $this->egp->id,
            'amount_out' => 1000,
            'amount_in' => 7500,
            'transaction_date' => now()->toDateString(),
            'description' => 'Transfer to Egypt',
        ])->assertRedirect();

        $this->assertEquals(9000, $this->sar->fresh()->balance());
        $this->assertEquals(59500, $this->egp->fresh()->balance());
        $this->assertSame(3, AccountTransaction::count());
        $this->assertSame(2, AccountTransaction::whereNotNull('transfer_group')->count());
    }

    public function test_client_payment_into_account_creates_inflow(): void
    {
        $this->actingAs($this->admin)->post(route('payments.store'), [
            'invoice_id' => $this->invoice->id,
            'amount' => 1500,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'account_id' => $this->sar->id,
            'exchange_rate' => 1,
        ])->assertRedirect(route('invoices.show', $this->invoice));

        $tx = AccountTransaction::where('category', 'client_payment')->firstOrFail();
        $this->assertSame('in', $tx->direction);
        $this->assertEquals(1500, (float) $tx->amount);
        $this->assertEquals(11500, $this->sar->fresh()->balance());
    }

    public function test_task_payout_uses_account_and_can_be_reversed(): void
    {
        $taskStatus = TaskStatus::create([
            'name_ar' => 'جديدة', 'name_en' => 'Todo', 'slug' => 'todo-tr',
            'color' => '#6B7280', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $task = Task::create([
            'project_id' => $this->project->id, 'title' => 'Design', 'status_id' => $taskStatus->id,
            'priority' => 'medium', 'assigned_to' => $this->admin->id, 'actual_hours' => 0,
            'progress_percentage' => 0, 'cost_amount' => 3000, 'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('tasks.cost.payout', $task))
            ->assertOk()
            ->assertSee('Egypt Bank');

        $this->actingAs($this->admin)->post(route('tasks.cost.pay', $task), [
            'account_id' => $this->egp->id,
            'amount' => 3000,
            'exchange_rate' => 0.075,
            'payout_date' => now()->toDateString(),
        ])->assertRedirect(route('tasks.show', $task));

        $tx = AccountTransaction::where('category', 'team_payout')->firstOrFail();
        $this->assertSame('out', $tx->direction);
        $this->assertEquals(225, (float) $tx->base_amount);
        $this->assertNotNull($task->fresh()->cost_paid_at);

        $this->actingAs($this->admin)->post(route('tasks.cost.unpay', $task))->assertRedirect();
        $this->assertNull($task->fresh()->cost_paid_at);
        $this->assertSame(0, AccountTransaction::where('category', 'team_payout')->count());
    }

    public function test_reconciliation_toggle_and_export(): void
    {
        $tx = AccountTransaction::create([
            'account_id' => $this->sar->id, 'transaction_date' => now()->toDateString(),
            'direction' => 'in', 'amount' => 100, 'currency' => 'SAR', 'exchange_rate' => 1,
            'base_amount' => 100, 'category' => 'other', 'description' => 'Test',
        ]);

        $this->actingAs($this->admin)
            ->post(route('accounts.reconcile', [$this->sar, $tx]))
            ->assertRedirect();

        $this->assertNotNull($tx->fresh()->reconciled_at);

        $this->actingAs($this->admin)
            ->get(route('accounts.export', $this->sar))
            ->assertOk();
    }

    public function test_accounts_require_finance_permission(): void
    {
        $user = User::create([
            'name' => 'Member', 'email' => 'tr-member@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $user->assignRole('team-member');

        $this->actingAs($user)->get(route('accounts.index'))->assertForbidden();
    }
}
