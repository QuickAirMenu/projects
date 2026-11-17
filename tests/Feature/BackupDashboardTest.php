<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BackupDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected string $dir;

    protected string $script;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'backup@example.com', 'password' => 'password',
            'is_active' => true, 'language' => 'en',
        ]);
        $this->admin->assignRole('super-admin');

        $this->dir = storage_path('framework/testing/backups');
        File::ensureDirectoryExists($this->dir);
        File::cleanDirectory($this->dir);

        $this->script = $this->dir.'/backup-all.sh';
        File::put($this->script, "#!/bin/bash\necho \"backup ran\" > \"".$this->dir."/ran.txt\"\n");
        chmod($this->script, 0755);

        config([
            'backup.path' => $this->dir,
            'backup.script' => $this->script,
        ]);
    }

    protected function tearDown(): void
    {
        File::cleanDirectory($this->dir);
        parent::tearDown();
    }

    public function test_index_lists_backups_and_script_status(): void
    {
        File::put($this->dir.'/u227212075_ip_am_20260101_010101.sql.gz', 'dump');

        $this->actingAs($this->admin)
            ->get(route('settings.backups'))
            ->assertOk()
            ->assertSee('u227212075_ip_am_20260101_010101.sql.gz')
            ->assertSee(__('messages.backup_ready'));
    }

    public function test_backup_file_can_be_downloaded(): void
    {
        $name = 'u227212075_ip_am_20260101_010101.sql.gz';
        File::put($this->dir.'/'.$name, 'dump');

        $this->actingAs($this->admin)
            ->get(route('settings.backups.download', $name))
            ->assertOk()
            ->assertDownload($name);
    }

    public function test_missing_backup_returns_404(): void
    {
        $this->actingAs($this->admin)
            ->get(route('settings.backups.download', 'nope.sql.gz'))
            ->assertNotFound();
    }

    public function test_admin_can_run_backup_script(): void
    {
        $this->actingAs($this->admin)
            ->post(route('settings.backups.run'))
            ->assertRedirect()
            ->assertSessionHas('backup_output');

        $this->assertFileExists($this->dir.'/ran.txt');
    }
}
