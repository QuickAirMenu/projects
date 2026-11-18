<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Quote;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SearchFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $acme;

    protected Client $beta;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'search@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-search',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $this->acme = Client::create([
            'name' => 'John Smith', 'company_name' => 'Acme Holding',
            'email' => 'john@acme.test', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->beta = Client::create([
            'name' => 'Sara Ali', 'company_name' => 'Beta Studio',
            'email' => 'sara@beta.test', 'phone' => '0500000001',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->project = Project::create([
            'name' => 'Acme Website', 'client_id' => $this->acme->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $this->admin->id,
            'budget' => 5000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);

        foreach ([['INV-AAA-111', $this->acme], ['INV-BBB-222', $this->beta]] as [$number, $client]) {
            Invoice::create([
                'invoice_number' => $number, 'project_id' => $this->project->id, 'client_id' => $client->id,
                'invoice_type' => 'initial', 'invoice_date' => now()->toDateString(),
                'subtotal' => 100, 'tax_percentage' => 15, 'tax_amount' => 15, 'discount_amount' => 0,
                'total' => 115, 'paid_amount' => 0, 'remaining_amount' => 115,
                'payment_status' => 'unpaid', 'created_by' => $this->admin->id,
            ]);
        }

        foreach ([['QUO-AAA-111', 'Website design', $this->acme], ['QUO-BBB-222', 'Brand identity', $this->beta]] as [$number, $subject, $client]) {
            Quote::create([
                'quote_number' => $number, 'public_token' => Str::random(32), 'client_id' => $client->id,
                'subject' => $subject, 'subtotal' => 100, 'tax_percentage' => 15, 'tax_amount' => 15,
                'discount_amount' => 0, 'total' => 115, 'status' => 'draft',
                'valid_until' => now()->addDays(10)->toDateString(), 'created_by' => $this->admin->id,
            ]);
        }
    }

    public function test_invoices_can_be_searched_by_number_company_or_project(): void
    {
        $this->actingAs($this->admin)->get(route('invoices.index', ['q' => 'AAA-111']))
            ->assertOk()->assertSee('INV-AAA-111')->assertDontSee('INV-BBB-222');

        $this->actingAs($this->admin)->get(route('invoices.index', ['q' => 'Beta Studio']))
            ->assertOk()->assertSee('INV-BBB-222')->assertDontSee('INV-AAA-111');

        $this->actingAs($this->admin)->get(route('invoices.index', ['q' => 'Acme Website']))
            ->assertOk()->assertSee('INV-AAA-111');
    }

    public function test_quotes_can_be_searched_by_number_subject_or_client(): void
    {
        $this->actingAs($this->admin)->get(route('quotes.index', ['q' => 'BBB-222']))
            ->assertOk()->assertSee('QUO-BBB-222')->assertDontSee('QUO-AAA-111');

        $this->actingAs($this->admin)->get(route('quotes.index', ['q' => 'Brand identity']))
            ->assertOk()->assertSee('QUO-BBB-222');

        $this->actingAs($this->admin)->get(route('quotes.index', ['q' => 'Acme Holding']))
            ->assertOk()->assertSee('QUO-AAA-111');
    }
}
