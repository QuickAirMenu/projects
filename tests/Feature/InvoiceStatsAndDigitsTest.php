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

class InvoiceStatsAndDigitsTest extends TestCase
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
            'name' => 'Admin', 'email' => 'stats@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-stats',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'Stats Client', 'email' => 'stats-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Stats Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 5000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_invoice_stats_use_actual_outstanding_and_overdue(): void
    {
        $this->invoice(1000, 0, now()->addDays(5)->toDateString(), 'unpaid');
        $this->invoice(2000, 500, now()->addDays(5)->toDateString(), 'partially_paid');
        $this->invoice(1000, 0, now()->subDays(5)->toDateString(), 'unpaid');
        $this->invoice(500, 500, now()->subDays(5)->toDateString(), 'paid');

        $this->actingAs($this->admin)
            ->get(route('invoices.index'))
            ->assertOk()
            ->assertSee('3,500.00') // outstanding 1000+1500+1000
            ->assertSee('1 / 3');   // overdue count 1 / not fully paid 3
    }

    public function test_sync_command_fixes_stale_overdue_status(): void
    {
        $invoice = $this->invoice(1000, 0, now()->subDays(5)->toDateString(), 'unpaid');

        $this->artisan('invoices:sync-statuses')->assertSuccessful();

        $this->assertSame('overdue', $invoice->fresh()->payment_status);
    }

    public function test_arabic_numerals_are_accepted_in_payment_form(): void
    {
        $invoice = $this->invoice(1000, 0, now()->toDateString(), 'unpaid');

        $this->actingAs($this->admin)->post(route('payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => '١٬٠٠٠٫٥٠',
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ])->assertRedirect(route('invoices.show', $invoice));

        $payment = Payment::firstOrFail();
        $this->assertEquals(1000.50, (float) $payment->amount);
    }

    protected function invoice(float $total, float $paid, ?string $due, string $status = 'unpaid'): Invoice
    {
        return Invoice::create([
            'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(), 'due_date' => $due,
            'subtotal' => $total, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => $total, 'paid_amount' => $paid, 'remaining_amount' => $total - $paid,
            'payment_status' => $status, 'created_by' => $this->admin->id,
        ]);
    }
}
