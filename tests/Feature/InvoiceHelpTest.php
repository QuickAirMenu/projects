<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InvoiceHelpTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'ihelp@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_invoice_stat_help_is_hidden_as_tooltip(): void
    {
        $html = $this->actingAs($this->admin)->get(route('invoices.index'))->assertOk()->getContent();

        $this->assertStringNotContainsString('“Outstanding” is the remaining amount', $html);
        $this->assertStringContainsString('ip-tip-wrap', $html);
        $this->assertStringContainsString(__('finance.outstanding_help'), $html);
        $this->assertStringContainsString(__('finance.overdue_help'), $html);
    }
}
