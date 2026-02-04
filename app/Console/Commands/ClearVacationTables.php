<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearVacationTables extends Command
{
    protected $signature = 'app:clear-vacation-tables {--force : Force the operation to run without confirmation}';
    protected $description = 'Truncates tables related to vacations module: entitlements, request_entitlements, and solicitudes.';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('¿Estás SEGURO de que quieres borrar TODAS las vacaciones, solicitudes y saldos? Esta acción no se puede deshacer.')) {
            $this->info('Operación cancelada.');
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->info('Truncating request_entitlements (Pivot)...');
        DB::table('request_entitlements')->truncate();

        $this->info('Truncating SolicitudVacaciones...');
        DB::table('solicitudes_vacaciones')->truncate();

        $this->info('Truncating Entitlements (Saldos Unificados)...');
        DB::table('entitlements')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('¡Tablas de vacaciones limpiadas correctamente!');
    }
}
