<?php

namespace Tests\Feature;

use App\Mail\PaymentReviewedMail;
use App\Models\Client;
use App\Models\ClientPortalAccount;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\PaymentReviewed;
use App\Services\InvoiceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PaymentClientNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected ClientPortalAccount $account;

    protected Invoice $invoice;

    protected Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Setting::updateOrCreate(
            ['key' => 'mail_host'],
            ['value' => 'smtp.example.com', 'type' => 'string', 'group' => 'mail']
        );

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'notifadmin@example.com', 'password' => 'password',
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
            'name' => 'Notify Client', 'email' => 'client-notify@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->account = ClientPortalAccount::create([
            'client_id' => $this->client->id, 'password' => 'secret-password',
            'is_active' => true, 'email_verified_at' => now(),
        ]);

        $project = Project::create([
            'name' => 'Notify Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);

        $this->invoice = Invoice::create([
            'project_id' => $project->id, 'client_id' => $this->client->id, 'invoice_type' => 'initial',
            'invoice_date' => now()->toDateString(), 'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 1000, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin);

        $this->payment = app(InvoiceService::class)->recordPayment($this->invoice, [
            'amount' => 400,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
        ]);
    }

    public function test_approval_notifies_client_in_portal_and_email(): void
    {
        Notification::fake();
        Mail::fake();

        $this->actingAs($this->admin)
            ->post(route('payments.approve', $this->payment))
            ->assertRedirect();

        Notification::assertSentTo($this->account, PaymentReviewed::class, function (PaymentReviewed $n) {
            return $n->payment->status === 'approved';
        });

        Mail::assertSent(PaymentReviewedMail::class, function (PaymentReviewedMail $mail) {
            return $mail->hasTo($this->client->email) && $mail->payment->status === 'approved';
        });
    }

    public function test_rejection_notifies_client_with_reason(): void
    {
        Notification::fake();
        Mail::fake();

        $this->actingAs($this->admin)
            ->post(route('payments.reject', $this->payment), [
                'rejection_reason' => 'Receipt unreadable',
            ])
            ->assertRedirect();

        Notification::assertSentTo($this->account, PaymentReviewed::class, function (PaymentReviewed $n) {
            $data = $n->toArray($this->account);

            return $n->payment->status === 'rejected'
                && str_contains($data['message'], 'Receipt unreadable');
        });

        Mail::assertSent(PaymentReviewedMail::class, function (PaymentReviewedMail $mail) {
            return $mail->payment->status === 'rejected'
                && $mail->payment->rejection_reason === 'Receipt unreadable';
        });
    }

    public function test_portal_notifications_page_and_mark_all_read(): void
    {
        $this->payment->update(['status' => 'approved', 'reviewed_by' => $this->admin->id, 'reviewed_at' => now()]);
        $this->account->notify(new PaymentReviewed($this->payment->fresh(['invoice'])));

        $this->actingAs($this->account, 'client');

        $this->get(route('portal.notifications'))
            ->assertOk()
            ->assertSee('Payment approved');

        $this->post(route('portal.notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $this->account->fresh()->unreadNotifications()->count());
    }
}
