<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\InvoiceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InvoiceTotalsRecalculationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'pay@example.com', 'password' => 'password',
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
            'name' => 'Pay Client', 'email' => 'payclient@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $project = Project::create([
            'name' => 'Pay Project', 'client_id' => $client->id, 'service_type_id' => $service->id,
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
    }

    public function test_deleting_payment_recalculates_invoice_totals(): void
    {
        $this->actingAs($this->admin);

        $payment = app(InvoiceService::class)->recordPayment($this->invoice, [
            'amount' => 400,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ]);

        $this->invoice->refresh();
        $this->assertSame('partially_paid', $this->invoice->payment_status);
        $this->assertEquals(400.0, (float) $this->invoice->paid_amount);

        $this->actingAs($this->admin)
            ->delete(route('payments.destroy', $payment))
            ->assertRedirect(route('invoices.show', $this->invoice));

        $this->invoice->refresh();
        $this->assertSame('unpaid', $this->invoice->payment_status);
        $this->assertEquals(0.0, (float) $this->invoice->paid_amount);
        $this->assertEquals(1000.0, (float) $this->invoice->remaining_amount);
    }

    public function test_restoring_payment_recalculates_invoice_totals(): void
    {
        $this->actingAs($this->admin);

        $payment = app(InvoiceService::class)->recordPayment($this->invoice, [
            'amount' => 1000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ]);

        $this->invoice->refresh();
        $this->assertSame('paid', $this->invoice->payment_status);

        $payment->delete();
        app(InvoiceService::class)->recalculateInvoiceTotals($this->invoice);

        $this->invoice->refresh();
        $this->assertSame('unpaid', $this->invoice->payment_status);

        $this->actingAs($this->admin)
            ->post(route('payments.restore', $payment->id))
            ->assertRedirect(route('payments.index'));

        $this->invoice->refresh();
        $this->assertSame('paid', $this->invoice->payment_status);
        $this->assertEquals(1000.0, (float) $this->invoice->paid_amount);
        $this->assertEquals(0.0, (float) $this->invoice->remaining_amount);
    }
}
