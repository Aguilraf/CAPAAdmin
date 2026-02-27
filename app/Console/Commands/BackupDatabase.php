<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--type=daily : The type of backup (daily, instant, custom)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database using mysqldump';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $database = config('database.connections.mysql.database');
        $port = config('database.connections.mysql.port', 3306);

        // Determine filename based on type
        $type = $this->option('type');
        $path = storage_path('app/backups');

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        if ($type === 'instant') {
            $filename = "backup_latest_change.sql";
        } else {
            // Daily or custom
            $date = Carbon::now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$database}_{$date}.sql";
        }

        $filepath = "{$path}/{$filename}";

        // Find mysqldump
        $mysqldump = $this->findMysqldump();

        if (!$mysqldump) {
            $this->error('mysqldump not found. Please ensure MySQL is installed or add it to system PATH.');
            return 1;
        }

        $binPath = dirname($mysqldump);

        // Try to find my.ini
        // Usually in mysql root (parent of bin)
        $basePath = dirname($binPath);
        $iniPath = $basePath . DIRECTORY_SEPARATOR . 'my.ini';

        $defaultsArg = '';
        if (file_exists($iniPath)) {
            $defaultsArg = "--defaults-file=\"{$iniPath}\"";
        }

        // Build robust Windows command
        // cmd /c allows setting local env variables and chaining
        // We set PATH to include binPath first, trigger cd, then run mysqldump
        // Note: Using "2>&1" to capture stderr

        $cmd = "cmd /c \"cd /d \"{$binPath}\" && set PATH={$binPath};%PATH% && mysqldump {$defaultsArg} --user=\"{$username}\" --password=\"{$password}\" --host=\"{$host}\" --port=\"{$port}\" \"{$database}\" --result-file=\"{$filepath}\"\" 2>&1";

        // Execute
        $output = null;
        $resultCode = null;
        \exec($cmd, $output, $resultCode);

        if ($resultCode === 0) {
            $this->info("Backup successful: {$filename}");
            return 0;
        } else {
            $this->error("Backup failed with code {$resultCode}");
            $this->error("Mysqldump path used: {$mysqldump}");
            $this->error("Command: {$cmd}"); // Debug info
            $this->error(implode("\n", $output));
            return 1;
        }
    }

    private function findMysqldump()
    {
        // Validation for common paths - Prioritize Laragon since we are likely using it
        $commonPaths = [
            'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql*\\bin\\mysqldump.exe',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp64\\bin\\mysql\\mysql*\\bin\\mysqldump.exe',
            'D:\\xampp\\mysql\\bin\\mysqldump.exe',
        ];

        // First check exact paths or explicit patterns
        foreach ($commonPaths as $path) {
            // Check for wildcards
            if (strpos($path, '*') !== false) {
                $found = glob($path);
                if ($found && count($found) > 0) {
                    return $found[0];
                }
            } elseif (file_exists($path)) {
                return $path;
            }
        }

        // Glob search for dynamic versions if not found above
        $globPatterns = [
            'C:\\laragon\\bin\\mysql\\mysql*\\bin\\mysqldump.exe',
            'C:\\wamp64\\bin\\mysql\\mysql*\\bin\\mysqldump.exe',
        ];

        foreach ($globPatterns as $pattern) {
            $found = glob($pattern);
            if ($found && count($found) > 0) {
                return $found[0];
            }
        }

        // Check if global as last resort
        $output = null;
        $resultCode = null;
        \exec('mysqldump --version', $output, $resultCode);
        if ($resultCode === 0) {
            return 'mysqldump';
        }

        return false;
    }
}
