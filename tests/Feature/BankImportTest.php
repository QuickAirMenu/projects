<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\BankImport;
use App\Models\User;
use App\Services\BankStatementImportService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BankImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'bankimp@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $this->account = Account::create([
            'name' => 'Saudi Bank', 'type' => 'bank', 'currency' => 'SAR',
            'opening_balance' => 0, 'exchange_rate_to_base' => 1, 'is_active' => true,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_parser_handles_semicolon_dates_and_amounts(): void
    {
        $file = $this->csv("Date;Description;Amount\n19/11/2026;Client;1.234,56\n19/11/2026;Team;(225,00)\n");

        $parsed = app(BankStatementImportService::class)->parse($file);

        $this->assertCount(2, $parsed['rows']);
        $this->assertSame('2026-11-19', $parsed['rows'][0]['date']);
        $this->assertEquals(1234.56, $parsed['rows'][0]['amount']);
        $this->assertSame('in', $parsed['rows'][0]['direction']);
        $this->assertSame('out', $parsed['rows'][1]['direction']);
        $this->assertEquals(225, $parsed['rows'][1]['amount']);
    }

    public function test_preview_and_confirm_match_existing_and_create_new(): void
    {
        $existing = AccountTransaction::create([
            'account_id' => $this->account->id, 'transaction_date' => now()->toDateString(),
            'direction' => 'in', 'amount' => 1000, 'currency' => 'SAR', 'exchange_rate' => 1,
            'base_amount' => 1000, 'category' => 'client_payment', 'description' => 'Existing transfer',
        ]);

        $content = "Date,Description,Amount\n".now()->toDateString().",Client transfer,1000.00\n".now()->toDateString().",Team payout,(500.00)\n";
        $file = $this->csv($content);

        $this->actingAs($this->admin)
            ->post(route('accounts.import.preview', $this->account), ['file' => $file])
            ->assertOk()
            ->assertSee(__('accounts.import_new'));

        $this->actingAs($this->admin)
            ->post(route('accounts.import.confirm', $this->account), ['include' => [0, 1]])
            ->assertRedirect(route('accounts.show', $this->account));

        $this->assertNotNull($existing->fresh()->reconciled_at);
        $this->assertSame(1, BankImport::firstOrFail()->matched_count);
        $this->assertSame(1, BankImport::firstOrFail()->created_count);
        $this->assertSame(2, AccountTransaction::count());

        $created = AccountTransaction::where('direction', 'out')->firstOrFail();
        $this->assertEquals(500, (float) $created->amount);
        $this->assertNotNull($created->reconciled_at);
    }

    public function test_duplicate_rows_are_skipped(): void
    {
        AccountTransaction::create([
            'account_id' => $this->account->id, 'transaction_date' => now()->toDateString(),
            'direction' => 'in', 'amount' => 700, 'currency' => 'SAR', 'exchange_rate' => 1,
            'base_amount' => 700, 'category' => 'other', 'description' => 'Already imported',
            'reconciled_at' => now(),
        ]);

        $file = $this->csv("Date,Description,Amount\n".now()->toDateString().",Duplicate,700.00\n");

        $this->actingAs($this->admin)->post(route('accounts.import.preview', $this->account), ['file' => $file])
            ->assertOk()
            ->assertSee(__('accounts.import_duplicate'));

        $this->actingAs($this->admin)->post(route('accounts.import.confirm', $this->account), ['include' => [0]])
            ->assertRedirect();

        $this->assertSame(1, AccountTransaction::count());
    }

    public function test_import_requires_permission(): void
    {
        $user = User::create([
            'name' => 'Member', 'email' => 'imp-member@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $user->assignRole('team-member');

        $this->actingAs($user)->get(route('accounts.import.create', $this->account))->assertForbidden();
    }

    protected function csv(string $content, string $name = 'statement.csv'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, $content);
    }
}
