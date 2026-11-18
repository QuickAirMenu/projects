<?php

namespace Tests\Feature;

use App\Mail\InvoiceReminderMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceReminder;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AutoReminderTest extends TestCase
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

        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => 'smtp.example.com', 'type' => 'string', 'group' => 'mail']);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'auto-rem@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-rem',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'Reminder Client', 'email' => 'auto-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Reminder Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_overdue_reminders_are_sent_and_recorded_once_per_interval(): void
    {
        Mail::fake();
        $invoice = $this->overdueInvoice();

        $this->artisan('invoices:send-reminders')->assertSuccessful();

        Mail::assertSent(InvoiceReminderMail::class, fn ($mail) => $mail->hasTo('auto-client@example.com'));
        $this->assertSame(1, $invoice->reminders()->count());
        $this->assertSame('overdue', $invoice->fresh()->payment_status);

        $this->artisan('invoices:send-reminders')->assertSuccessful();
        Mail::assertSent(InvoiceReminderMail::class, 1);
        $this->assertSame(1, $invoice->reminders()->count());

        $this->travel(8)->days();
        $this->artisan('invoices:send-reminders')->assertSuccessful();
        Mail::assertSent(InvoiceReminderMail::class, 2);
        $this->assertSame(2, $invoice->reminders()->count());
    }

    public function test_paid_or_not_due_invoices_are_ignored(): void
    {
        Mail::fake();
        $this->overdueInvoice(['payment_status' => 'paid', 'paid_amount' => 1000, 'remaining_amount' => 0]);
        $this->overdueInvoice(['due_date' => now()->addDays(5)->toDateString()]);

        $this->artisan('invoices:send-reminders')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertSame(0, InvoiceReminder::count());
    }

    public function test_command_fails_gracefully_without_mail_configuration(): void
    {
        Mail::fake();
        $this->overdueInvoice();
        Setting::where('key', 'mail_host')->delete();
        config(['mail.mailers.smtp.host' => null]);

        $this->artisan('invoices:send-reminders')->assertFailed();

        Mail::assertNothingSent();
        $this->assertSame(0, InvoiceReminder::count());
    }

    protected function overdueInvoice(array $overrides = []): Invoice
    {
        return Invoice::create(array_merge([
            'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'invoice_type' => 'initial', 'invoice_date' => now()->subDays(40)->toDateString(),
            'due_date' => now()->subDays(10)->toDateString(),
            'subtotal' => 1000, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ], $overrides));
    }
}
