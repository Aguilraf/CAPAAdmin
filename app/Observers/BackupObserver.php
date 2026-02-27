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
        // El comando de backup está deshabilitado temporalmente debido a restricciones
        // de funciones de sistema (exec) en el servidor de producción que causan errores 500.

        /* try {
            Artisan::call('db:backup', ['--type' => 'instant']);
        } catch (\Exception $e) {
            Log::error('Backup on change failed: ' . $e->getMessage());
        } */
    }
}
