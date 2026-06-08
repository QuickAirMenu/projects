<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use App\Models\ProjectAttachment;
use App\Models\ProjectStatus;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Setting;
use App\Models\ServiceType;
use App\Models\ServiceTypeItem;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use RuntimeException;

class CoreRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;
    protected ServiceType $serviceType;
    protected ProjectStatus $projectStatus;
    protected TaskStatus $taskStatus;
    protected Project $project;
    protected Quote $quote;
    protected Quote $convertibleQuote;
    protected Invoice $invoice;
    protected Contract $contract;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_active' => true,
            'language' => 'ar',
        ]);
        $this->user->assignRole('super-admin');

        $this->projectStatus = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ',
            'name_en' => 'In Progress',
            'slug' => 'in-progress',
            'color' => '#3B82F6',
            'sort_order' => 1,
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->taskStatus = TaskStatus::create([
            'name_ar' => 'قيد العمل',
            'name_en' => 'In Progress',
            'slug' => 'in-progress',
            'color' => '#8B5CF6',
            'sort_order' => 1,
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->serviceType = ServiceType::create([
            'name_ar' => 'تصميم',
            'name_en' => 'Design',
            'description_ar' => 'خدمة تصميم',
            'description_en' => 'Design service',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        ServiceTypeItem::create([
            'service_type_id' => $this->serviceType->id,
            'description' => 'Homepage Design',
            'quantity' => 1,
            'unit_price' => 500,
            'sort_order' => 1,
        ]);

        $this->client = Client::create([
            'name' => 'Acme',
            'company_name' => 'Acme Ltd',
            'email' => 'client@example.com',
            'phone' => '0500000000',
            'whatsapp' => '0500000000',
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        $this->project = Project::create([
            'name' => 'Website Build',
            'client_id' => $this->client->id,
            'service_type_id' => $this->serviceType->id,
            'priority' => 'medium',
            'status_id' => $this->projectStatus->id,
            'manager_id' => $this->user->id,
            'budget' => 1000,
            'payment_status' => 'unpaid',
            'progress_percentage' => 20,
            'created_by' => $this->user->id,
        ]);

        $this->project->teamMembers()->attach($this->user->id);

        ProjectAttachment::create([
            'project_id' => $this->project->id,
            'uploaded_by' => $this->user->id,
            'file_name' => 'brief.pdf',
            'file_path' => 'projects/brief.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 1234,
        ]);

        $this->task = Task::create([
            'project_id' => $this->project->id,
            'title' => 'Design mockup',
            'description' => 'Create initial mockup',
            'status_id' => $this->taskStatus->id,
            'priority' => 'high',
            'assigned_to' => $this->user->id,
            'progress_percentage' => 50,
            'created_by' => $this->user->id,
        ]);

        $this->quote = Quote::create([
            'client_id' => $this->client->id,
            'subject' => 'Landing page quote',
            'description' => 'Quote desc',
            'subtotal' => 500,
            'tax_percentage' => 15,
            'tax_amount' => 75,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 575,
            'status' => 'approved',
            'valid_until' => now()->addDays(10),
            'created_by' => $this->user->id,
        ]);
        QuoteItem::create([
            'quote_id' => $this->quote->id,
            'service_type_id' => $this->serviceType->id,
            'description' => 'Homepage Design',
            'quantity' => 1,
            'unit_price' => 500,
            'total' => 500,
            'sort_order' => 1,
        ]);

        $this->convertibleQuote = Quote::create([
            'client_id' => $this->client->id,
            'subject' => 'Conversion quote',
            'description' => 'Convertible quote',
            'subtotal' => 500,
            'tax_percentage' => 15,
            'tax_amount' => 75,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 575,
            'status' => 'approved',
            'valid_until' => now()->addDays(10),
            'created_by' => $this->user->id,
        ]);
        QuoteItem::create([
            'quote_id' => $this->convertibleQuote->id,
            'service_type_id' => $this->serviceType->id,
            'description' => 'Homepage Design',
            'quantity' => 1,
            'unit_price' => 500,
            'total' => 500,
            'sort_order' => 1,
        ]);

        $this->invoice = Invoice::create([
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
            'quote_id' => $this->quote->id,
            'invoice_type' => 'initial',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 500,
            'tax_percentage' => 15,
            'tax_amount' => 75,
            'discount_amount' => 0,
            'total' => 575,
            'paid_amount' => 0,
            'remaining_amount' => 575,
            'payment_status' => 'unpaid',
            'created_by' => $this->user->id,
        ]);
        InvoiceItem::create([
            'invoice_id' => $this->invoice->id,
            'description' => 'Homepage Design',
            'quantity' => 1,
            'unit_price' => 500,
            'total' => 500,
            'sort_order' => 1,
        ]);

        $this->contract = Contract::create([
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
            'invoice_id' => $this->invoice->id,
            'title' => 'Main contract',
            'description' => 'Contract desc',
            'start_date' => now()->toDateString(),
            'contract_value' => 575,
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_quotes_pages_and_public_link_work(): void
    {
        $this->actingAs($this->user);

        $this->get(route('quotes.index'))->assertOk()->assertSee('معتمد وغير محوّل');
        $this->get(route('quotes.index'))->assertOk()->assertSee('قيمة غير محوّلة');
        $this->get(route('quotes.create'))
            ->assertOk()
            ->assertSee('service_type_id', false);

        $this->get(route('quotes.show', $this->convertibleQuote))
            ->assertOk()
            ->assertSee('إرسال عبر WhatsApp')
            ->assertSee('https://wa.me/966500000000?text=', false)
            ->assertSee(route('quotes.pdf', $this->convertibleQuote), false)
            ->assertSee('convert-to-invoice', false);

        $this->get(route('quotes.public', $this->convertibleQuote->public_token))
            ->assertOk()
            ->assertSee($this->convertibleQuote->quote_number)
            ->assertSee('الإجمالي');
    }

    public function test_quotes_use_phone_as_whatsapp_fallback_and_send_email_with_smtp(): void
    {
        $this->actingAs($this->user);
        Mail::fake();

        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => 'smtp.example.com', 'type' => 'string', 'group' => 'mail']);

        $client = Client::create([
            'name' => 'Fallback Client',
            'email' => 'fallback@example.com',
            'phone' => '0551234567',
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        $draftQuote = Quote::create([
            'client_id' => $client->id,
            'subject' => 'Draft share quote',
            'description' => 'Draft quote',
            'subtotal' => 500,
            'tax_percentage' => 15,
            'tax_amount' => 75,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'total' => 575,
            'status' => 'draft',
            'valid_until' => now()->addDays(10),
            'created_by' => $this->user->id,
        ]);

        QuoteItem::create([
            'quote_id' => $draftQuote->id,
            'service_type_id' => $this->serviceType->id,
            'description' => 'Homepage Design',
            'quantity' => 1,
            'unit_price' => 500,
            'total' => 500,
            'sort_order' => 1,
        ]);

        $this->get(route('quotes.show', $draftQuote))
            ->assertOk()
            ->assertSee('إرسال عبر WhatsApp')
            ->assertSee('https://wa.me/966551234567?text=', false);

        $this->get(route('quotes.public', $draftQuote->public_token))
            ->assertNotFound();

        $this->post(route('quotes.send', $draftQuote))
            ->assertRedirect(route('quotes.show', $draftQuote));

        Mail::assertSent(\App\Mail\QuoteSentMail::class);
        $this->assertSame('sent', $draftQuote->fresh()->status);
    }

    public function test_quote_send_failure_does_not_expose_raw_exception_text(): void
    {
        $this->actingAs($this->user);

        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => 'smtp.example.com', 'type' => 'string', 'group' => 'mail']);

        Mail::shouldReceive('to')->andReturn(new class {
            public function send($mail): void
            {
                throw new RuntimeException('SMTP boom');
            }
        });

        $this->post(route('quotes.send', $this->convertibleQuote))
            ->assertRedirect(route('quotes.show', $this->convertibleQuote))
            ->assertSessionHas('success', function (string $message): bool {
                return str_contains($message, 'تعذر إرسال الإيميل') && ! str_contains($message, 'SMTP boom');
            });
    }

    public function test_seeded_demo_credentials_work_and_seed_output_marks_them_demo_only(): void
    {
        $this->artisan('db:seed', [
            '--class' => \Database\Seeders\DatabaseSeeder::class,
            '--force' => true,
        ])->expectsOutputToContain('Demo credentials only (not for production):');

        $this->assertDatabaseHas('users', ['email' => 'admin@airmenu.net']);

        $this->post('/login', [
            'email' => 'admin@airmenu.net',
            'password' => UserSeeder::DEMO_PASSWORD,
        ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_client_store_sends_portal_invite_with_smtp(): void
    {
        $this->actingAs($this->user);
        Mail::fake();

        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => 'smtp.example.com', 'type' => 'string', 'group' => 'mail']);

        $this->post(route('clients.store'), [
            'name' => 'Invite Client',
            'email' => 'invite@example.com',
            'phone' => '0501112222',
            'status' => 'active',
        ])->assertRedirect(route('clients.index'));

        $client = Client::where('email', 'invite@example.com')->firstOrFail();
        $this->assertDatabaseHas('client_portal_accounts', [
            'client_id' => $client->id,
            'is_active' => 1,
        ]);

        Mail::assertSent(\App\Mail\ClientWelcomeMail::class);
    }

    public function test_quote_can_convert_to_invoice_and_invoice_create_prefills_quote_data(): void
    {
        $this->actingAs($this->user);

        $this->post(route('quotes.convert-to-invoice', $this->convertibleQuote))
            ->assertRedirect(route('invoices.create', [
                'quote_id' => $this->convertibleQuote->id,
                'client_id' => $this->client->id,
                'description' => $this->convertibleQuote->subject,
            ]));

        $this->get(route('invoices.create', [
            'quote_id' => $this->convertibleQuote->id,
            'client_id' => $this->client->id,
            'description' => $this->convertibleQuote->subject,
        ]))
            ->assertOk()
            ->assertSee($this->convertibleQuote->quote_number)
            ->assertSee('name="quote_id"', false)
            ->assertSee('Homepage Design');
    }

    public function test_invoices_pages_and_pdf_work(): void
    {
        $this->actingAs($this->user);

        $this->get(route('invoices.index'))->assertOk()->assertSee('المحصّل');
        $this->get(route('invoices.show', $this->invoice))
            ->assertOk()
            ->assertSee($this->invoice->invoice_number)
            ->assertSee('Record Payment')
            ->assertSee('name="proof_of_payment"', false)
            ->assertSee(route('invoices.pdf', $this->invoice), false);

        $this->get(route('invoices.pdf', $this->invoice))
            ->assertOk();
    }

    public function test_contracts_pages_and_pdf_work(): void
    {
        $this->actingAs($this->user);

        $this->get(route('contracts.index'))->assertOk();
        $this->get(route('contracts.create', [
            'invoice_id' => $this->invoice->id,
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
        ]))
            ->assertOk()
            ->assertSee('تم استيراد بيانات الفاتورة')
            ->assertSee($this->invoice->invoice_number);

        $this->get(route('contracts.show', $this->contract))
            ->assertOk()
            ->assertSee($this->contract->contract_number)
            ->assertSee($this->invoice->invoice_number)
            ->assertSee(route('contracts.pdf', $this->contract), false);

        $this->get(route('contracts.pdf', $this->contract))->assertOk();
    }

    public function test_project_and_task_pages_work(): void
    {
        $this->actingAs($this->user);

        $this->get(route('projects.index'))->assertOk();
        $this->get(route('projects.board'))->assertOk();
        $this->get(route('projects.show', $this->project))
            ->assertOk()
            ->assertSee($this->project->name)
            ->assertSee('brief.pdf')
            ->assertSee($this->user->name);

        $this->get(route('tasks.index'))->assertOk();
        $this->get(route('tasks.kanban'))->assertOk();
        $this->get(route('tasks.show', $this->task))
            ->assertOk()
            ->assertSee($this->task->title)
            ->assertSee($this->user->name)
            ->assertSee(route('tasks.edit', $this->task), false);
    }

    public function test_settings_page_is_accessible_to_authorized_user(): void
    {
        $this->actingAs($this->user);

        $this->get(route('settings.index'))->assertOk();
    }
}
