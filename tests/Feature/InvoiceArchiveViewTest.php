<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InvoiceArchiveViewTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'invarch@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-arch',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'Arch Client', 'email' => 'arch-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Arch Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_trashed_invoice_can_be_viewed_and_force_deleted_when_unlinked(): void
    {
        $invoice = $this->invoice();
        $invoice->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 100, 'total' => 100, 'sort_order' => 0]);
        $invoice->delete();

        $this->actingAs($this->admin)
            ->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertSee(__('messages.invoice_archived_hint'))
            ->assertSee(route('invoices.force-delete', $invoice), false);

        $this->actingAs($this->admin)->get(route('invoices.pdf', $invoice))->assertOk();

        $this->actingAs($this->admin)
            ->delete(route('invoices.force-delete', $invoice))
            ->assertRedirect(route('invoices.index', ['trashed' => 1]));

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('invoice_items', ['invoice_id' => $invoice->id]);
    }

    public function test_linked_invoice_cannot_be_force_deleted(): void
    {
        $invoice = $this->invoice();
        Payment::create([
            'invoice_id' => $invoice->id, 'payment_number' => 'PAY-ARCH-1',
            'amount' => 50, 'payment_date' => now()->toDateString(), 'payment_method' => 'cash',
            'status' => 'approved', 'received_by' => $this->admin->id,
        ]);
        $invoice->delete();

        $this->actingAs($this->admin)
            ->delete(route('invoices.force-delete', $invoice))
            ->assertSessionHas('error');

        $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id]);
    }

    public function test_trashed_invoice_edit_is_blocked(): void
    {
        $invoice = $this->invoice();
        $invoice->delete();

        $this->actingAs($this->admin)->get(route('invoices.edit', $invoice))->assertNotFound();
    }

    protected function invoice(): Invoice
    {
        return Invoice::create([
            'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(),
            'subtotal' => 100, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 100, 'paid_amount' => 0, 'remaining_amount' => 100,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);
    }
}
