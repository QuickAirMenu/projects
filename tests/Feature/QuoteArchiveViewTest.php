<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Quote;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class QuoteArchiveViewTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Quote $quote;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'archive@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $client = Client::create([
            'name' => 'Archive Client', 'email' => 'archive-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);

        $this->quote = Quote::create([
            'quote_number' => 'QUO-ARCH-1', 'public_token' => Str::random(32),
            'client_id' => $client->id, 'subject' => 'Archived quote subject',
            'subtotal' => 100, 'tax_percentage' => 0, 'tax_amount' => 0, 'discount_amount' => 0,
            'total' => 100, 'status' => 'draft', 'valid_until' => now()->addDays(5)->toDateString(),
            'created_by' => $this->admin->id,
        ]);
        $this->quote->delete();
    }

    public function test_trashed_quote_can_be_viewed_and_restored(): void
    {
        $this->actingAs($this->admin)
            ->get(route('quotes.show', $this->quote))
            ->assertOk()
            ->assertSee('Archived quote subject')
            ->assertSee(__('messages.quote_archived_hint'))
            ->assertSee(route('quotes.restore', $this->quote), false)
            ->assertSee(route('quotes.force-delete', $this->quote), false);

        $this->actingAs($this->admin)
            ->get(route('quotes.pdf', $this->quote))
            ->assertOk();
    }

    public function test_trashed_quote_edit_still_blocked(): void
    {
        $this->actingAs($this->admin)
            ->get(route('quotes.edit', $this->quote))
            ->assertNotFound();
    }
}
