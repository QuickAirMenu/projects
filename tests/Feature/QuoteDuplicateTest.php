<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Quote;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class QuoteDuplicateTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected Quote $quote;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'dup@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $this->client = Client::create([
            'name' => 'Dup Client', 'email' => 'dup-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);

        $this->quote = Quote::create([
            'client_id' => $this->client->id,
            'subject' => 'Original Quote',
            'description' => 'Original description',
            'subtotal' => 1000, 'tax_percentage' => 15, 'tax_amount' => 150,
            'discount_percentage' => 0, 'discount_amount' => 0,
            'total' => 1150, 'status' => 'approved',
            'valid_until' => now()->addDays(10),
            'terms_and_conditions' => 'Terms here',
            'created_by' => $this->admin->id,
        ]);

        $this->quote->items()->create([
            'description' => 'Item A', 'quantity' => 2, 'unit_price' => 250, 'total' => 500, 'sort_order' => 0,
        ]);
        $this->quote->items()->create([
            'description' => 'Item B', 'quantity' => 1, 'unit_price' => 500, 'total' => 500, 'sort_order' => 1,
        ]);
    }

    public function test_quote_can_be_duplicated_with_items(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('quotes.duplicate', $this->quote));
        $response->assertRedirect();

        $copy = Quote::where('id', '!=', $this->quote->id)->latest('id')->firstOrFail();

        $this->assertSame('draft', $copy->status);
        $this->assertStringContainsString('Original Quote', $copy->subject);
        $this->assertNotSame($this->quote->quote_number, $copy->quote_number);
        $this->assertCount(2, $copy->items);
        $this->assertSame('Item A', $copy->items->first()->description);
        $this->assertSame('Terms here', $copy->terms_and_conditions);
    }

    public function test_items_json_returns_lines(): void
    {
        $this->actingAs($this->admin);

        $this->getJson(route('quotes.items', $this->quote))
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.description', 'Item A')
            ->assertJsonPath('0.quantity', 2);
    }
}
