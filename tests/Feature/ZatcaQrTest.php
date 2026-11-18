<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientPortalAccount;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Setting;
use App\Models\User;
use App\Services\ZatcaQrService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ZatcaQrTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Setting::updateOrCreate(['key' => 'company_name_ar'], ['value' => 'شركة إي بروجكتس', 'group' => 'general']);
        Setting::updateOrCreate(['key' => 'vat_number'], ['value' => '310123456700003', 'group' => 'general']);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'zatca@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-zatca',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->client = Client::create([
            'name' => 'ZATCA Client', 'email' => 'zatca-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $project = Project::create([
            'name' => 'ZATCA Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);

        $this->invoice = Invoice::create([
            'project_id' => $project->id, 'client_id' => $this->client->id, 'invoice_type' => 'initial',
            'invoice_date' => now()->toDateString(), 'subtotal' => 100, 'tax_percentage' => 15,
            'tax_amount' => 15, 'discount_amount' => 0, 'total' => 115, 'paid_amount' => 0,
            'remaining_amount' => 115, 'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);
    }

    public function test_invoice_gets_uuid_automatically(): void
    {
        $second = Invoice::create([
            'project_id' => $this->invoice->project_id, 'client_id' => $this->client->id,
            'invoice_type' => 'additional', 'invoice_date' => now()->toDateString(),
            'subtotal' => 10, 'tax_percentage' => 15, 'tax_amount' => 1.5, 'discount_amount' => 0,
            'total' => 11.5, 'paid_amount' => 0, 'remaining_amount' => 11.5,
            'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
        ]);

        $this->assertNotEmpty($this->invoice->uuid);
        $this->assertNotSame($this->invoice->uuid, $second->uuid);
    }

    public function test_qr_payload_contains_required_tlv_tags(): void
    {
        $service = app(ZatcaQrService::class);
        $payload = base64_decode($service->payload($this->invoice), true);

        $tags = [];
        $i = 0;
        while ($i < strlen($payload)) {
            $tag = ord($payload[$i]);
            $len = ord($payload[$i + 1]);
            $tags[$tag] = substr($payload, $i + 2, $len);
            $i += 2 + $len;
        }

        $this->assertSame('شركة إي بروجكتس', $tags[1]);
        $this->assertSame('310123456700003', $tags[2]);
        $this->assertNotEmpty($tags[3]);
        $this->assertSame('115.00', $tags[4]);
        $this->assertSame('15.00', $tags[5]);
    }

    public function test_portal_invoice_shows_qr_and_uuid(): void
    {
        Storage::fake('private');
        $account = ClientPortalAccount::create([
            'client_id' => $this->client->id, 'password' => 'secret-password',
            'is_active' => true, 'email_verified_at' => now(),
        ]);

        $this->actingAs($account, 'client')
            ->get(route('portal.invoices.show', $this->invoice))
            ->assertOk()
            ->assertSee('data:image/svg+xml;base64,', false)
            ->assertSee($this->invoice->uuid);
    }

    public function test_admin_invoice_shows_qr(): void
    {
        $this->actingAs($this->admin)
            ->get(route('invoices.show', $this->invoice))
            ->assertOk()
            ->assertSee('data:image/svg+xml;base64,', false)
            ->assertSee('310123456700003');
    }
}
