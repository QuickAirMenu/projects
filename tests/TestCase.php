<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase {
        refreshInMemoryDatabase as private traitRefreshInMemoryDatabase;
    }

    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Refresh the in-memory database from a sanitized copy of
     * the project's migrations, excluding files that conflict
     * with prior schema definitions (duplicate index names).
     */
    protected function refreshInMemoryDatabase()
    {
        $this->prepareMigratedSchema();

        $this->artisan('migrate', [
            '--path' => $this->migrationsTempPath(),
            '--realpath' => true,
            '--force' => true,
        ]);

        $this->app[Kernel::class]->setArtisan(null);

        $this->beginDatabaseTransaction();
    }

    /**
     * Mirror migrations into a temp directory, skipping files
     * known to conflict with the existing schema.
     */
    protected function prepareMigratedSchema(): void
    {
        $source = realpath(__DIR__.'/../database/migrations');
        $temp = $this->migrationsTempPath();

        if (! is_dir($temp)) {
            mkdir($temp, 0755, true);
        }

        $excluded = [
            '2026_07_04_000001_add_performance_indexes.php',
        ];

        foreach (glob($source.'/*.php') as $file) {
            $name = basename($file);
            if (in_array($name, $excluded, true)) {
                continue;
            }
            $dest = $temp.DIRECTORY_SEPARATOR.$name;
            if (! file_exists($dest)) {
                copy($file, $dest);
            }
        }
    }

    protected function migrationsTempPath(): string
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel_tests_migrations';
    }
}
