<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientPortalAccount;
use App\Models\Invoice;
use App\Models\Payment;
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

class PortalReceiptUploadTest extends TestCase
{
    use RefreshDatabase;

    protected Client $client;

    protected ClientPortalAccount $account;

    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin2@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);

        $this->client = Client::create([
            'name' => 'Receipt Client', 'email' => 'receipt@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $admin->id,
        ]);
        $this->account = ClientPortalAccount::create([
            'client_id' => $this->client->id, 'password' => 'secret-password',
            'is_active' => true, 'email_verified_at' => now(),
        ]);

        $project = Project::create([
            'name' => 'Receipt Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $admin->id,
        ]);

        $this->invoice = Invoice::create([
            'project_id' => $project->id, 'client_id' => $this->client->id, 'invoice_type' => 'initial',
            'invoice_date' => now()->toDateString(), 'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 1000, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000,
            'payment_status' => 'unpaid', 'created_by' => $admin->id,
        ]);
    }

    public function test_portal_invoice_page_is_accessible_for_owner(): void
    {
        $this->actingAs($this->account, 'client');

        $this->get(route('portal.invoices.show', $this->invoice))
            ->assertOk()
            ->assertSee($this->invoice->invoice_number)
            ->assertSee('Bank transfer')
            ->assertSee('Upload transfer receipt');
    }

    public function test_client_cannot_access_another_clients_invoice(): void
    {
        $other = Client::create([
            'name' => 'Other', 'email' => 'other@example.com', 'phone' => '0500000001',
            'status' => 'active', 'created_by' => 1,
        ]);
        $otherAccount = ClientPortalAccount::create([
            'client_id' => $other->id, 'password' => 'secret-password',
            'is_active' => true, 'email_verified_at' => now(),
        ]);

        $this->actingAs($otherAccount, 'client');

        $this->get(route('portal.invoices.show', $this->invoice))->assertNotFound();
    }

    public function test_client_can_upload_bank_transfer_receipt(): void
    {
        $this->actingAs($this->account, 'client');

        $file = UploadedFile::fake()->image('receipt.png');

        $this->post(route('portal.invoices.receipt', $this->invoice), [
            'amount' => 400,
            'payment_date' => now()->toDateString(),
            'transaction_reference' => 'TRX-123',
            'notes' => 'Bank transfer',
            'receipt' => $file,
        ])->assertRedirect(route('portal.invoices.show', $this->invoice));

        $payment = Payment::latest('id')->first();
        $this->assertNotNull($payment);
        $this->assertSame('bank_transfer', $payment->payment_method);
        $this->assertSame('pending', $payment->status);
        $this->assertEquals(400.0, (float) $payment->amount);
        $this->assertNotNull($payment->proof_file_path);
        Storage::disk('private')->assertExists($payment->proof_file_path);

        // Invoice must NOT change until admin approves
        $this->invoice->refresh();
        $this->assertEquals(0.0, (float) $this->invoice->paid_amount);
        $this->assertEquals(1000.0, (float) $this->invoice->remaining_amount);
        $this->assertSame('unpaid', $this->invoice->payment_status);
    }

    public function test_amount_is_capped_to_remaining_balance(): void
    {
        $this->actingAs($this->account, 'client');

        $this->post(route('portal.invoices.receipt', $this->invoice), [
            'amount' => 5000,
            'payment_date' => now()->toDateString(),
            'receipt' => UploadedFile::fake()->image('receipt.jpg'),
        ])->assertRedirect(route('portal.invoices.show', $this->invoice));

        $payment = Payment::latest('id')->first();
        $this->assertSame('pending', $payment->status);
        $this->assertEquals(1000.0, (float) $payment->amount);

        $this->invoice->refresh();
        $this->assertSame('unpaid', $this->invoice->payment_status);
        $this->assertEquals(0.0, (float) $this->invoice->paid_amount);
    }
}
