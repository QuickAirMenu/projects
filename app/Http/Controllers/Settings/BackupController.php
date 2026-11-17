<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    public function index()
    {
        $path = (string) config('backup.path');
        $script = (string) config('backup.script');

        $files = collect(glob(rtrim($path, '/').'/*.sql.gz') ?: [])
            ->map(function ($file) {
                $name = basename($file);

                return [
                    'name' => $name,
                    'size' => @filesize($file) ?: 0,
                    'date' => @filemtime($file) ?: 0,
                    'database' => preg_replace('/_\d{8}_\d{6}\.sql\.gz$/', '', $name),
                ];
            })
            ->sortByDesc('date')
            ->values();

        $latest = $files->groupBy('database')->map->first();

        $freeSpace = is_dir($path) ? @disk_free_space($path) : null;

        return view('settings.backups', [
            'files' => $files,
            'latest' => $latest,
            'scriptExists' => is_file($script),
            'scriptExecutable' => is_executable($script),
            'path' => $path,
            'freeSpace' => $freeSpace,
            'retentionDays' => (int) config('backup.retention_days', 30),
        ]);
    }

    public function run(Request $request)
    {
        $script = (string) config('backup.script');

        abort_unless(is_file($script), 404);
        abort_unless(is_executable($script), 403);

        @set_time_limit(300);

        $process = new Process(['bash', $script]);
        $process->setTimeout(280);
        $process->run();
        $output = $process->getOutput().$process->getErrorOutput();

        activity()->causedBy($request->user())->log('manual backup executed');

        $failed = str_contains($output, 'FAILED');

        return back()->with(
            $failed ? 'error' : 'success',
            $failed ? __('messages.backup_failed') : __('messages.backup_completed')
        )->with('backup_output', Str::limit(trim($output), 4000));
    }

    public function download(string $file)
    {
        $base = basename($file);

        abort_unless(str_ends_with($base, '.sql.gz'), 404);

        $dir = realpath((string) config('backup.path'));
        $full = $dir ? realpath($dir.DIRECTORY_SEPARATOR.$base) : false;

        abort_unless($dir && $full && str_starts_with($full, $dir.DIRECTORY_SEPARATOR) && is_file($full), 404);

        return response()->download($full);
    }
}
