<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TreasuryDashboardPdfTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Account $sar;

    protected Account $egp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'tdash@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $this->sar = Account::create([
            'name' => 'Saudi Bank', 'type' => 'bank', 'currency' => 'SAR',
            'opening_balance' => 5000, 'exchange_rate_to_base' => 1, 'is_active' => true,
            'created_by' => $this->admin->id,
        ]);
        $this->egp = Account::create([
            'name' => 'Egypt Bank', 'type' => 'bank', 'currency' => 'EGP',
            'opening_balance' => 10000, 'exchange_rate_to_base' => 0.075, 'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        AccountTransaction::create([
            'account_id' => $this->sar->id, 'transaction_date' => now()->toDateString(),
            'direction' => 'in', 'amount' => 2000, 'currency' => 'SAR', 'exchange_rate' => 1,
            'base_amount' => 2000, 'category' => 'client_payment', 'description' => 'Client deposit',
        ]);
        AccountTransaction::create([
            'account_id' => $this->egp->id, 'transaction_date' => now()->toDateString(),
            'direction' => 'out', 'amount' => 3000, 'currency' => 'EGP', 'exchange_rate' => 0.075,
            'base_amount' => 225, 'category' => 'team_payout', 'description' => 'Team payout',
        ]);
    }

    public function test_dashboard_shows_flows_chart_currencies_and_recent(): void
    {
        $this->actingAs($this->admin)
            ->get(route('accounts.index'))
            ->assertOk()
            ->assertSee(__('accounts.inflow_month'))
            ->assertSee(__('accounts.outflow_month'))
            ->assertSee(__('accounts.last_6_months'))
            ->assertSee(__('accounts.recent_movements'))
            ->assertSee('Client deposit')
            ->assertSee('2,000.00')
            ->assertSee('SAR')
            ->assertSee('EGP');
    }

    public function test_account_statement_pdf_renders_with_period(): void
    {
        $response = $this->actingAs($this->admin)->get(route('accounts.statement', [
            'account' => $this->sar,
            'from' => now()->subMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
