<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientPortalAccount;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\SupportTicketCreated;
use App\Notifications\SupportTicketReplied;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected ClientPortalAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'tickets@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $client = Client::create([
            'name' => 'Ticket Client', 'email' => 'ticket-client@example.com', 'phone' => '0500000000',
            'status' => 'active', 'created_by' => $this->admin->id,
        ]);
        $this->account = ClientPortalAccount::create([
            'client_id' => $client->id, 'password' => 'secret-password',
            'is_active' => true, 'email_verified_at' => now(),
        ]);
    }

    public function test_client_can_create_ticket_and_admin_is_notified(): void
    {
        Notification::fake();

        $this->actingAs($this->account, 'client');

        $this->post(route('portal.tickets.store'), [
            'subject' => 'Invoice question',
            'message' => 'Please clarify the VAT amount.',
            'priority' => 'high',
        ])->assertRedirect(route('portal.tickets'));

        $ticket = SupportTicket::firstOrFail();
        $this->assertSame('open', $ticket->status);
        $this->assertSame('high', $ticket->priority);
        $this->assertSame($this->account->client_id, $ticket->client_id);

        Notification::assertSentTo($this->admin, SupportTicketCreated::class);

        $this->get(route('portal.tickets'))
            ->assertOk()
            ->assertSee('Invoice question');
    }

    public function test_admin_can_reply_and_client_is_notified(): void
    {
        Notification::fake();

        $ticket = SupportTicket::create([
            'client_id' => $this->account->client_id,
            'portal_account_id' => $this->account->id,
            'subject' => 'Need help',
            'message' => 'Help me please',
            'priority' => 'normal',
            'status' => 'open',
        ]);

        $this->actingAs($this->admin)
            ->post(route('support-tickets.reply', $ticket), [
                'admin_reply' => 'We are on it.',
            ])
            ->assertRedirect();

        $ticket->refresh();
        $this->assertSame('answered', $ticket->status);
        $this->assertSame('We are on it.', $ticket->admin_reply);
        $this->assertSame($this->admin->id, $ticket->replied_by);

        Notification::assertSentTo($this->account, SupportTicketReplied::class);

        $this->actingAs($this->account, 'client')
            ->get(route('portal.tickets'))
            ->assertOk()
            ->assertSee('We are on it.');
    }

    public function test_admin_can_close_ticket(): void
    {
        $ticket = SupportTicket::create([
            'client_id' => $this->account->client_id,
            'portal_account_id' => $this->account->id,
            'subject' => 'Close me',
            'message' => 'x',
            'priority' => 'low',
            'status' => 'open',
        ]);

        $this->actingAs($this->admin)
            ->post(route('support-tickets.close', $ticket))
            ->assertRedirect();

        $this->assertSame('closed', $ticket->fresh()->status);
    }
}
