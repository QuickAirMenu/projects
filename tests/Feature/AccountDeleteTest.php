<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AccountDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'del@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_account_without_transactions_can_be_deleted(): void
    {
        $account = $this->account();

        $this->actingAs($this->admin)
            ->get(route('accounts.show', $account))
            ->assertOk()
            ->assertSee(__('messages.delete'));

        $this->actingAs($this->admin)
            ->delete(route('accounts.destroy', $account))
            ->assertRedirect(route('accounts.index'));

        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
    }

    public function test_account_with_transactions_cannot_be_deleted(): void
    {
        $account = $this->account();
        AccountTransaction::create([
            'account_id' => $account->id, 'transaction_date' => now()->toDateString(),
            'direction' => 'in', 'amount' => 100, 'currency' => 'SAR', 'exchange_rate' => 1,
            'base_amount' => 100, 'category' => 'other', 'description' => 'x',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('accounts.destroy', $account))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('accounts', ['id' => $account->id]);

        $this->actingAs($this->admin)
            ->get(route('accounts.show', $account))
            ->assertOk()
            ->assertSee(__('accounts.has_movements'));
    }

    protected function account(array $overrides = []): Account
    {
        return Account::create(array_merge([
            'name' => 'Temp Account', 'type' => 'cash', 'currency' => 'SAR',
            'opening_balance' => 0, 'exchange_rate_to_base' => 1, 'is_active' => true,
            'created_by' => $this->admin->id,
        ], $overrides));
    }
}
