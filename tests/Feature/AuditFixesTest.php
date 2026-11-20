<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\SupportTicket;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuditFixesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'auditfix@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-fix',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'Fix Client', 'email' => 'fix-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Fix Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_sync_statuses_clamps_negative_totals_and_marks_paid(): void
    {
        $invoice = Invoice::create([
            'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(),
            'subtotal' => -250, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => -250, 'paid_amount' => 0, 'remaining_amount' => -250,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);

        $this->artisan('invoices:sync-statuses')->assertSuccessful();

        $invoice->refresh();
        $this->assertEquals(0, (float) $invoice->total);
        $this->assertEquals(0, (float) $invoice->remaining_amount);
        $this->assertSame('paid', $invoice->payment_status);
    }

    public function test_prune_missing_files_command(): void
    {
        $payment = Payment::create([
            'invoice_id' => Invoice::create([
                'project_id' => $this->project->id, 'client_id' => $this->client->id,
                'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(),
                'subtotal' => 100, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
                'total' => 100, 'paid_amount' => 100, 'remaining_amount' => 0,
                'payment_status' => 'paid', 'created_by' => $this->admin->id,
            ])->id,
            'payment_number' => 'PAY-FIX-1', 'amount' => 100, 'payment_date' => now()->toDateString(),
            'payment_method' => 'cash', 'status' => 'approved', 'received_by' => $this->admin->id,
            'proof_file_path' => 'payments/missing.pdf', 'proof_file_name' => 'missing.pdf',
        ]);

        $expense = Expense::create([
            'expense_number' => 'EXP-FIX-1', 'vendor_name' => 'V', 'category' => 'other',
            'expense_date' => now()->toDateString(), 'amount' => 50, 'tax_amount' => 0, 'total' => 50,
            'status' => 'approved', 'receipt_path' => 'expenses/missing.pdf', 'receipt_name' => 'missing.pdf',
            'created_by' => $this->admin->id,
        ]);

        $ticket = SupportTicket::create([
            'client_id' => $this->client->id, 'subject' => 'T', 'message' => 'x',
            'priority' => 'normal', 'status' => 'open',
        ]);
        Storage::disk('private')->put('tickets/keep.pdf', 'ok');
        $keep = $ticket->attachments()->create([
            'file_name' => 'keep.pdf', 'file_path' => 'tickets/keep.pdf', 'file_size' => 2,
        ]);
        $gone = $ticket->attachments()->create([
            'file_name' => 'gone.pdf', 'file_path' => 'tickets/gone.pdf', 'file_size' => 2,
        ]);

        $document = ClientDocument::create([
            'client_id' => $this->client->id, 'type' => 'other', 'title' => 'Doc',
            'file_path' => 'clients/missing.pdf', 'file_name' => 'missing.pdf', 'created_by' => $this->admin->id,
        ]);

        $this->artisan('files:prune-missing', ['--force' => true])->assertSuccessful();

        $this->assertNull($payment->fresh()->proof_file_path);
        $this->assertNull($expense->fresh()->receipt_path);
        $this->assertDatabaseMissing('support_ticket_attachments', ['id' => $gone->id]);
        $this->assertDatabaseHas('support_ticket_attachments', ['id' => $keep->id]);
        $this->assertDatabaseMissing('client_documents', ['id' => $document->id]);
    }
}
