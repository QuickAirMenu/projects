<?php

namespace Tests\Feature;

use App\Mail\InvoiceIssuedMail;
use App\Models\Client;
use App\Models\ClientPortalAccount;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Setting;
use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TicketAttachmentAndInvoiceMailTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected ClientPortalAccount $account;

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'f5-admin@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $this->client = Client::create([
            'name' => 'F5 Client', 'email' => 'f5-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->account = ClientPortalAccount::create([
            'client_id' => $this->client->id, 'password' => 'secret-password',
            'is_active' => true, 'email_verified_at' => now(),
        ]);
    }

    public function test_client_can_attach_files_to_ticket(): void
    {
        $this->actingAs($this->account, 'client');

        $file = UploadedFile::fake()->create('contract.pdf', 120, 'application/pdf');

        $this->post(route('portal.tickets.store'), [
            'subject' => 'Attached contract',
            'message' => 'Please check the attached file.',
            'priority' => 'normal',
            'attachments' => [$file],
        ])->assertRedirect(route('portal.tickets'));

        $ticket = SupportTicket::firstOrFail();
        $this->assertCount(1, $ticket->attachments);
        Storage::disk('private')->assertExists($ticket->attachments->first()->file_path);

        $this->actingAs($this->account, 'client')
            ->get(route('portal.tickets'))
            ->assertOk()
            ->assertSee('contract.pdf');
    }

    public function test_attachment_download_is_scoped_to_owner(): void
    {
        $this->actingAs($this->account, 'client');
        $this->post(route('portal.tickets.store'), [
            'subject' => 'Mine', 'message' => 'x', 'priority' => 'low',
            'attachments' => [UploadedFile::fake()->image('photo.png')],
        ]);
        $attachment = SupportTicketAttachment::firstOrFail();

        $other = Client::create([
            'name' => 'Other', 'email' => 'other@example.com', 'phone' => '0500000001',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $otherAccount = ClientPortalAccount::create([
            'client_id' => $other->id, 'password' => 'secret-password',
            'is_active' => true, 'email_verified_at' => now(),
        ]);

        $this->actingAs($otherAccount, 'client')
            ->get(route('portal.tickets.attachment', $attachment))
            ->assertNotFound();

        $this->actingAs($this->account, 'client')
            ->get(route('portal.tickets.attachment', $attachment))
            ->assertOk();
    }

    public function test_admin_can_download_attachment(): void
    {
        $ticket = SupportTicket::create([
            'client_id' => $this->client->id, 'portal_account_id' => $this->account->id,
            'subject' => 'Admin dl', 'message' => 'x', 'priority' => 'normal', 'status' => 'open',
        ]);
        Storage::disk('private')->put('tickets/'.$ticket->id.'/note.txt', 'hello');
        $att = $ticket->attachments()->create([
            'file_name' => 'note.txt', 'file_path' => 'tickets/'.$ticket->id.'/note.txt',
            'file_size' => 5, 'mime' => 'text/plain',
        ]);

        $this->actingAs($this->admin)
            ->get(route('support-tickets.attachment', $att))
            ->assertOk();
    }

    public function test_invoice_creation_emails_client_when_smtp_configured(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => 'smtp.example.com', 'type' => 'string', 'group' => 'mail']);

        [$project] = $this->makeProject();

        $this->actingAs($this->admin)
            ->post(route('invoices.store'), [
                'project_id' => $project->id,
                'client_id' => $this->client->id,
                'invoice_type' => 'initial',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(15)->toDateString(),
                'items' => [
                    ['description' => 'Design work', 'quantity' => 2, 'unit_price' => 500],
                ],
            ])
            ->assertRedirect();

        Mail::assertSent(InvoiceIssuedMail::class, function (InvoiceIssuedMail $mail) {
            return $mail->hasTo('f5-client@example.com');
        });
    }

    public function test_invoice_creation_skips_email_when_smtp_missing(): void
    {
        Mail::fake();
        Setting::where('key', 'mail_host')->delete();
        config(['mail.mailers.smtp.host' => null]);

        [$project] = $this->makeProject();

        $this->actingAs($this->admin)
            ->post(route('invoices.store'), [
                'project_id' => $project->id,
                'client_id' => $this->client->id,
                'invoice_type' => 'initial',
                'invoice_date' => now()->toDateString(),
                'items' => [
                    ['description' => 'Work', 'quantity' => 1, 'unit_price' => 100],
                ],
            ])
            ->assertRedirect();

        Mail::assertNothingSent();
    }

    protected function makeProject(): array
    {
        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-f5',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $project = Project::create([
            'name' => 'F5 Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);

        return [$project];
    }
}
