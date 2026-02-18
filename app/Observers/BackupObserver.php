<?php

namespace App\Observers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class BackupObserver
{
    /**
     * Handle events after a record change.
     */
    public function created($model): void
    {
        $this->triggerBackup();
    }

    public function updated($model): void
    {
        $this->triggerBackup();
    }

    public function deleted($model): void
    {
        $this->triggerBackup();
    }

    public function restored($model): void
    {
        $this->triggerBackup();
    }

    public function forceDeleted($model): void
    {
        $this->triggerBackup();
    }

    protected function triggerBackup()
    {
        // Use Artisan::call to run the backup command
        // We run it in background or async if possible, but for simplicity here we run it directly.
        // To avoid blocking the user request too much, we might want to dispatch a Job if queue is set up.
        // Assuming queue is 'sync' (default dev), it will run synchronously.
        // If 'database' or 'redis' queue is used, we should dispatch a Job.

        // For now, let's run it directly but catch exceptions to not break the app flow
        try {
            Artisan::call('db:backup', ['--type' => 'instant']);
        } catch (\Exception $e) {
            Log::error('Backup on change failed: ' . $e->getMessage());
        }
    }
}
