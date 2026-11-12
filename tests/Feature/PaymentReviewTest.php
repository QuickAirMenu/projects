<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\InvoiceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PaymentReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Invoice $invoice;

    protected Payment $pendingPayment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'review@example.com', 'password' => 'password',
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
        $client = Client::create([
            'name' => 'Review Client', 'email' => 'rev@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $project = Project::create([
            'name' => 'Review Project', 'client_id' => $client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);

        $this->invoice = Invoice::create([
            'project_id' => $project->id, 'client_id' => $client->id, 'invoice_type' => 'initial',
            'invoice_date' => now()->toDateString(), 'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 1000, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin);

        $this->pendingPayment = app(InvoiceService::class)->recordPayment($this->invoice, [
            'amount' => 300,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
        ]);
    }

    public function test_pending_payment_does_not_affect_invoice(): void
    {
        $this->invoice->refresh();

        $this->assertSame('pending', $this->pendingPayment->status);
        $this->assertEquals(0.0, (float) $this->invoice->paid_amount);
        $this->assertEquals(1000.0, (float) $this->invoice->remaining_amount);
        $this->assertSame('unpaid', $this->invoice->payment_status);
    }

    public function test_admin_approval_records_payment_on_invoice(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('payments.approve', $this->pendingPayment))
            ->assertRedirect();

        $this->pendingPayment->refresh();
        $this->assertSame('approved', $this->pendingPayment->status);
        $this->assertSame($this->admin->id, $this->pendingPayment->reviewed_by);
        $this->assertNotNull($this->pendingPayment->reviewed_at);

        $this->invoice->refresh();
        $this->assertEquals(300.0, (float) $this->invoice->paid_amount);
        $this->assertEquals(700.0, (float) $this->invoice->remaining_amount);
        $this->assertSame('partially_paid', $this->invoice->payment_status);
    }

    public function test_admin_rejection_keeps_invoice_unchanged(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('payments.reject', $this->pendingPayment), [
            'rejection_reason' => 'Receipt is not readable',
        ])->assertRedirect();

        $this->pendingPayment->refresh();
        $this->assertSame('rejected', $this->pendingPayment->status);
        $this->assertSame('Receipt is not readable', $this->pendingPayment->rejection_reason);

        $this->invoice->refresh();
        $this->assertEquals(0.0, (float) $this->invoice->paid_amount);
        $this->assertSame('unpaid', $this->invoice->payment_status);
    }

    public function test_cannot_review_twice(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('payments.approve', $this->pendingPayment))->assertRedirect();
        $this->post(route('payments.reject', $this->pendingPayment))->assertRedirect();

        $this->assertSame('approved', $this->pendingPayment->fresh()->status);
    }
}
