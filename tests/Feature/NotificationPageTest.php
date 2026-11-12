<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\User;
use App\Notifications\PaymentReceived;
use App\Services\InvoiceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NotificationPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'notify@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_notifications_page_renders(): void
    {
        $this->seedNotification();

        $this->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('Payment received');
    }

    public function test_mark_all_as_read_works(): void
    {
        $this->seedNotification();

        $this->post(route('notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $this->admin->fresh()->unreadNotifications()->count());
    }

    public function test_unread_count_endpoint_returns_json(): void
    {
        $this->seedNotification();

        $this->get(route('notifications.count'))
            ->assertOk()
            ->assertJson(['count' => 1]);
    }

    private function seedNotification(): void
    {
        $this->actingAs($this->admin);

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $client = Client::create([
            'name' => 'Notify Client', 'email' => 'nc@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $project = Project::create([
            'name' => 'Notify Project', 'client_id' => $client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
        $invoice = Invoice::create([
            'project_id' => $project->id, 'client_id' => $client->id, 'invoice_type' => 'initial',
            'invoice_date' => now()->toDateString(), 'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 1000, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);

        $payment = app(InvoiceService::class)->recordPayment($invoice, [
            'amount' => 250,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ]);

        $this->admin->notify(new PaymentReceived($payment));
    }
}
