<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TransferProofTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Account $sar;

    protected Account $egp;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'proof@example.com', 'password' => 'password',
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
            'opening_balance' => 0, 'exchange_rate_to_base' => 0.075, 'is_active' => true,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_transfer_can_attach_proof_and_download_it(): void
    {
        $this->actingAs($this->admin)->post(route('accounts.transfer.store'), [
            'from_account_id' => $this->sar->id,
            'to_account_id' => $this->egp->id,
            'amount_out' => 1000,
            'amount_in' => 7500,
            'transaction_date' => now()->toDateString(),
            'description' => 'Monthly transfer',
            'attachment' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $out = AccountTransaction::where('account_id', $this->sar->id)->where('direction', 'out')->firstOrFail();
        $this->assertNotNull($out->attachment_path);
        Storage::disk('private')->assertExists($out->attachment_path);

        $this->actingAs($this->admin)
            ->get(route('accounts.attachment', [$this->sar, $out]))
            ->assertOk()
            ->assertDownload('transfer.pdf');

        $in = AccountTransaction::where('account_id', $this->egp->id)->where('direction', 'in')->firstOrFail();
        $this->assertNull($in->attachment_path);
    }

    public function test_transfer_rate_computes_received_amount(): void
    {
        $this->actingAs($this->admin)->post(route('accounts.transfer.store'), [
            'from_account_id' => $this->sar->id,
            'to_account_id' => $this->egp->id,
            'amount_out' => 1000,
            'rate' => 13.3,
            'transaction_date' => now()->toDateString(),
        ])->assertRedirect();

        $in = AccountTransaction::where('account_id', $this->egp->id)->where('direction', 'in')->firstOrFail();
        $this->assertEquals(13300, (float) $in->amount);
    }

    public function test_manual_transaction_can_attach_proof(): void
    {
        $this->actingAs($this->admin)->post(route('accounts.transactions.store', $this->egp), [
            'direction' => 'in',
            'amount' => 500,
            'transaction_date' => now()->toDateString(),
            'category' => 'client_payment',
            'description' => 'Client deposit',
            'attachment' => UploadedFile::fake()->image('receipt.jpg'),
        ])->assertRedirect();

        $tx = AccountTransaction::firstOrFail();
        $this->assertNotNull($tx->attachment_path);
        Storage::disk('private')->assertExists($tx->attachment_path);
    }

    public function test_proof_download_is_scoped_to_account(): void
    {
        $other = Account::create([
            'name' => 'Other', 'type' => 'cash', 'currency' => 'SAR',
            'opening_balance' => 0, 'exchange_rate_to_base' => 1, 'is_active' => true,
            'created_by' => $this->admin->id,
        ]);
        Storage::disk('private')->put('accounts/x/proof.pdf', 'data');
        $tx = AccountTransaction::create([
            'account_id' => $this->sar->id, 'transaction_date' => now()->toDateString(),
            'direction' => 'in', 'amount' => 10, 'currency' => 'SAR', 'exchange_rate' => 1,
            'base_amount' => 10, 'category' => 'other', 'attachment_path' => 'accounts/x/proof.pdf',
            'attachment_name' => 'proof.pdf',
        ]);

        $this->actingAs($this->admin)
            ->get(route('accounts.attachment', [$other, $tx]))
            ->assertNotFound();
    }
}
