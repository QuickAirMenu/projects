<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Quote;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class Client360Test extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected Project $project;

    protected Invoice $invoice;

    protected Quote $quote;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'client360@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-c360',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'John Smith', 'company_name' => 'Acme Holding',
            'email' => 'john@acme.test', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Acme Website', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 5000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);
        $this->quote = Quote::create([
            'quote_number' => 'QUO-9001', 'public_token' => Str::random(32),
            'client_id' => $this->client->id, 'subject' => 'Website proposal',
            'subtotal' => 500, 'tax_percentage' => 15, 'tax_amount' => 75,
            'discount_amount' => 0, 'total' => 575, 'status' => 'draft',
            'valid_until' => now()->addDays(14)->toDateString(), 'created_by' => $this->admin->id,
        ]);
        $this->invoice = Invoice::create([
            'project_id' => $this->project->id, 'client_id' => $this->client->id,
            'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(),
            'subtotal' => 500, 'tax_percentage' => 15, 'tax_amount' => 75, 'discount_amount' => 0,
            'total' => 575, 'paid_amount' => 0, 'remaining_amount' => 575,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);
    }

    public function test_client_page_shows_quotes_invoices_and_documents(): void
    {
        $this->actingAs($this->admin)
            ->get(route('clients.show', $this->client))
            ->assertOk()
            ->assertSee('QUO-9001')
            ->assertSee($this->invoice->invoice_number)
            ->assertSee('575.00')
            ->assertSee(__('client.documents'));
    }

    public function test_client_display_name_combines_company_and_person(): void
    {
        $this->assertSame('Acme Holding — John Smith', $this->client->display_name);

        $this->actingAs($this->admin)
            ->get(route('quotes.create'))
            ->assertOk()
            ->assertSee('data-searchable', false)
            ->assertSee('Acme Holding — John Smith');
    }

    public function test_document_can_be_uploaded_downloaded_and_deleted(): void
    {
        $this->actingAs($this->admin)
            ->post(route('clients.documents.store', $this->client), [
                'type' => 'commercial_registration',
                'title' => 'CR 2026',
                'number' => '1010123456',
                'issue_date' => now()->subYear()->toDateString(),
                'expiry_date' => now()->addYear()->toDateString(),
                'file' => UploadedFile::fake()->create('cr.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect();

        $document = ClientDocument::firstOrFail();
        Storage::disk('private')->assertExists($document->file_path);

        $this->actingAs($this->admin)
            ->get(route('clients.documents.download', [$this->client, $document]))
            ->assertOk()
            ->assertDownload('cr.pdf');

        $this->actingAs($this->admin)
            ->delete(route('clients.documents.destroy', [$this->client, $document]))
            ->assertRedirect();

        $this->assertDatabaseMissing('client_documents', ['id' => $document->id]);
        Storage::disk('private')->assertMissing($document->file_path);
    }

    public function test_document_type_and_mime_are_validated(): void
    {
        $this->actingAs($this->admin)
            ->post(route('clients.documents.store', $this->client), [
                'type' => 'passport',
                'title' => 'Bad',
                'file' => UploadedFile::fake()->create('bad.exe', 10, 'application/x-msdownload'),
            ])
            ->assertSessionHasErrors(['type', 'file']);
    }

    public function test_document_cannot_be_accessed_through_another_client(): void
    {
        $other = Client::create([
            'name' => 'Other Client', 'email' => 'other@acme.test', 'phone' => '0500000001',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        Storage::disk('private')->put('clients/x/cr.pdf', 'data');
        $document = ClientDocument::create([
            'client_id' => $other->id, 'type' => 'other', 'title' => 'Other doc',
            'file_path' => 'clients/x/cr.pdf', 'file_name' => 'cr.pdf', 'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('clients.documents.download', [$this->client, $document]))
            ->assertNotFound();
    }
}
