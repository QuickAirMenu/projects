<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AccountNatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'nature@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_each_type_has_correct_nature(): void
    {
        $liabilities = ['credit_card', 'loan'];
        $assets = ['bank', 'cash', 'wallet', 'payment_gateway', 'petty_cash', 'escrow', 'investment'];

        foreach ($liabilities as $type) {
            $this->assertSame('liability', (new Account(['type' => $type]))->nature(), $type);
        }

        foreach ($assets as $type) {
            $this->assertSame('asset', (new Account(['type' => $type]))->nature(), $type);
        }

        $this->assertSame('neutral', (new Account(['type' => 'other']))->nature());
    }

    public function test_index_shows_assets_liabilities_and_net(): void
    {
        $bank = Account::create([
            'name' => 'Saudi Bank', 'type' => 'bank', 'currency' => 'SAR',
            'opening_balance' => 10000, 'exchange_rate_to_base' => 1, 'is_active' => true,
            'created_by' => $this->admin->id,
        ]);
        $card = Account::create([
            'name' => 'Credit Card', 'type' => 'credit_card', 'currency' => 'SAR',
            'opening_balance' => 0, 'exchange_rate_to_base' => 1, 'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        AccountTransaction::create([
            'account_id' => $card->id, 'transaction_date' => now()->toDateString(),
            'direction' => 'out', 'amount' => 2000, 'currency' => 'SAR', 'exchange_rate' => 1,
            'base_amount' => 2000, 'category' => 'expense', 'description' => 'Card purchase',
        ]);

        $this->actingAs($this->admin)
            ->get(route('accounts.index'))
            ->assertOk()
            ->assertSee(__('accounts.total_assets', ['currency' => 'SAR']))
            ->assertSee(__('accounts.total_liabilities', ['currency' => 'SAR']))
            ->assertSee(__('accounts.nature_asset'))
            ->assertSee(__('accounts.nature_liability'))
            ->assertSee('10,000.00')
            ->assertSee('2,000.00')
            ->assertSee('8,000.00'); // net
    }
}
