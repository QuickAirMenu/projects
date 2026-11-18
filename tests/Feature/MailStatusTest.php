<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MailStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'mail-status@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');
    }

    public function test_settings_page_shows_unconfigured_badge(): void
    {
        $this->actingAs($this->admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee(__('messages.mail_test_unconfigured'));
    }

    public function test_mail_test_records_success(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => 'smtp.example.com', 'type' => 'string', 'group' => 'mail']);
        Setting::updateOrCreate(['key' => 'mail_from_address'], ['value' => 'from@example.com', 'type' => 'string', 'group' => 'mail']);

        $this->actingAs($this->admin)
            ->post(route('settings.mail-test'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('1', Setting::where('key', 'mail_test_ok')->value('value'));
        $this->assertNotNull(Setting::where('key', 'mail_test_at')->value('value'));

        $this->actingAs($this->admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee(__('messages.mail_test_ok'));
    }

    public function test_connection_check_records_failure_for_unreachable_host(): void
    {
        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => '127.0.0.1', 'type' => 'string', 'group' => 'mail']);
        Setting::updateOrCreate(['key' => 'mail_port'], ['value' => '1', 'type' => 'string', 'group' => 'mail']);
        Setting::updateOrCreate(['key' => 'mail_encryption'], ['value' => 'none', 'type' => 'string', 'group' => 'mail']);

        $this->actingAs($this->admin)
            ->post(route('settings.mail-connection'))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('0', Setting::where('key', 'mail_conn_ok')->value('value'));
    }

    public function test_mail_test_sends_to_custom_recipient(): void
    {
        Mail::fake();
        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => 'smtp.example.com', 'type' => 'string', 'group' => 'mail']);

        $this->actingAs($this->admin)
            ->post(route('settings.mail-test'), ['recipient' => 'custom@example.com'])
            ->assertSessionHas('success');

        $this->assertSame('1', Setting::where('key', 'mail_test_ok')->value('value'));
    }

    public function test_mail_test_records_failure_without_configuration(): void
    {
        Mail::fake();
        config(['mail.mailers.smtp.host' => null]);

        $this->actingAs($this->admin)
            ->post(route('settings.mail-test'))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('0', Setting::where('key', 'mail_test_ok')->value('value'));
        Mail::assertNothingSent();
    }
}
