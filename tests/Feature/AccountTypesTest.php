<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AccountTypesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'types@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_all_account_types_are_available_and_creatable(): void
    {
        foreach (Account::TYPES as $index => $type) {
            $this->actingAs($this->admin)->post(route('accounts.store'), [
                'name' => 'Account '.$type,
                'type' => $type,
                'currency' => 'SAR',
                'opening_balance' => 0,
                'exchange_rate_to_base' => 1,
                'is_active' => 1,
            ])->assertRedirect(route('accounts.index'));
        }

        $this->assertSame(count(Account::TYPES), Account::count());
    }

    public function test_create_page_lists_every_type(): void
    {
        $response = $this->actingAs($this->admin)->get(route('accounts.create'))->assertOk();

        foreach (Account::TYPES as $type) {
            $response->assertSee('value="'.$type.'"', false);
        }
    }

    public function test_unknown_type_is_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('accounts.store'), [
            'name' => 'Bad', 'type' => 'crypto', 'currency' => 'SAR',
        ])->assertSessionHasErrors('type');
    }
}
