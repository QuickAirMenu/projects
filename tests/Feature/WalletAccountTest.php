<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WalletAccountTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'wallet@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_wallet_account_type_can_be_created(): void
    {
        $this->actingAs($this->admin)->post(route('accounts.store'), [
            'name' => 'Vodafone Cash',
            'type' => 'wallet',
            'currency' => 'EGP',
            'opening_balance' => 500,
            'exchange_rate_to_base' => 0.075,
            'is_active' => 1,
        ])->assertRedirect(route('accounts.index'));

        $account = Account::firstOrFail();
        $this->assertSame('wallet', $account->type);

        $this->actingAs($this->admin)
            ->get(route('accounts.create'))
            ->assertOk()
            ->assertSee('value="wallet"', false)
            ->assertSee(__('accounts.type_wallet'));
    }
}
