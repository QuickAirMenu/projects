<?php

namespace Tests\Feature;

use App\Models\Client;
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

class QuoteForceDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'forceq@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $this->client = Client::create([
            'name' => 'Force Client', 'email' => 'force-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
    }

    public function test_trashed_quote_can_be_force_deleted_with_items(): void
    {
        $quote = $this->quote();
        $quote->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 100, 'total' => 100, 'sort_order' => 0]);
        $quote->delete();

        $this->actingAs($this->admin)
            ->delete(route('quotes.force-delete', $quote))
            ->assertRedirect(route('quotes.index', ['trashed' => 1]));

        $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
        $this->assertDatabaseMissing('quote_items', ['quote_id' => $quote->id]);
    }

    public function test_active_quote_cannot_be_force_deleted(): void
    {
        $quote = $this->quote();

        $this->actingAs($this->admin)
            ->delete(route('quotes.force-delete', $quote))
            ->assertNotFound();

        $this->assertDatabaseHas('quotes', ['id' => $quote->id]);
    }

    public function test_force_delete_requires_permission(): void
    {
        $quote = $this->quote();
        $quote->delete();

        $user = User::create([
            'name' => 'Member', 'email' => 'force-member@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $user->assignRole('team-member');

        $status = ProjectStatus::create([
            'name_ar' => 'قيد التنفيذ', 'name_en' => 'In Progress', 'slug' => 'in-progress-qperm',
            'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $service = ServiceType::create([
            'name_ar' => 'تصميم', 'name_en' => 'Design', 'description_ar' => 'x', 'description_en' => 'x',
            'is_active' => true, 'sort_order' => 1,
        ]);
        Project::create([
            'name' => 'Accessible Project', 'client_id' => $this->client->id, 'service_type_id' => $service->id,
            'priority' => 'medium', 'status_id' => $status->id, 'manager_id' => $user->id,
            'budget' => 1000, 'payment_status' => 'unpaid', 'progress_percentage' => 0,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($user)->delete(route('quotes.force-delete', $quote))->assertForbidden();
    }

    protected function quote(): Quote
    {
        return Quote::create([
            'quote_number' => 'QUO-FORCE-1', 'public_token' => Str::random(32),
            'client_id' => $this->client->id, 'subject' => 'Force quote',
            'subtotal' => 100, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 100, 'status' => 'draft', 'valid_until' => now()->addDays(10)->toDateString(),
            'created_by' => $this->admin->id,
        ]);
    }
}
