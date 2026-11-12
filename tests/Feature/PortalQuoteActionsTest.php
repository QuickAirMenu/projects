<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientPortalAccount;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Quote;
use App\Models\ServiceType;
use App\Models\User;
use App\Notifications\PaymentSubmitted;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PortalQuoteActionsTest extends TestCase
{
    use RefreshDatabase;

    protected Client $client;

    protected ClientPortalAccount $account;

    protected Quote $quote;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin3@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);

        $this->client = Client::create([
            'name' => 'Quote Client', 'email' => 'qclient@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->account = ClientPortalAccount::create([
            'client_id' => $this->client->id, 'password' => 'secret-password',
            'is_active' => true, 'email_verified_at' => now(),
        ]);

        $project = Project::create([
            'name' => 'Quote Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);

        Invoice::create([
            'project_id' => $project->id, 'client_id' => $this->client->id, 'invoice_type' => 'initial',
            'invoice_date' => now()->toDateString(), 'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 1000, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);

        $this->quote = Quote::create([
            'client_id' => $this->client->id,
            'subject' => 'Portal Approval Quote',
            'description' => 'x',
            'subtotal' => 500, 'tax_percentage' => 0, 'tax_amount' => 0,
            'discount_percentage' => 0, 'discount_amount' => 0,
            'total' => 500, 'status' => 'sent',
            'valid_until' => now()->addDays(10),
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_client_can_approve_sent_quote(): void
    {
        $this->actingAs($this->account, 'client');

        $this->post(route('portal.quotes.approve', $this->quote))
            ->assertRedirect();

        $this->quote->refresh();
        $this->assertSame('approved', $this->quote->status);
        $this->assertNotNull($this->quote->approved_at);
    }

    public function test_client_can_reject_sent_quote(): void
    {
        $this->actingAs($this->account, 'client');

        $this->post(route('portal.quotes.reject', $this->quote))
            ->assertRedirect();

        $this->assertSame('rejected', $this->quote->fresh()->status);
    }

    public function test_client_cannot_act_on_another_clients_quote(): void
    {
        $other = Client::create([
            'name' => 'Other', 'email' => 'other2@example.com', 'phone' => '0500000001',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $otherAccount = ClientPortalAccount::create([
            'client_id' => $other->id, 'password' => 'secret-password',
            'is_active' => true, 'email_verified_at' => now(),
        ]);

        $this->actingAs($otherAccount, 'client');

        $this->post(route('portal.quotes.approve', $this->quote))->assertNotFound();
    }

    public function test_receipt_upload_notifies_finance_and_admins(): void
    {
        Notification::fake();

        $this->actingAs($this->account, 'client');

        $invoice = Invoice::first();

        $this->post(route('portal.invoices.receipt', $invoice), [
            'amount' => 100,
            'payment_date' => now()->toDateString(),
            'receipt' => UploadedFile::fake()->image('receipt.png'),
        ])->assertRedirect();

        $payment = Payment::latest('id')->first();
        $this->assertNotNull($payment);

        Notification::assertSentTo($this->admin, PaymentSubmitted::class);
    }
}
