<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearVacationTables extends Command
{
    protected $signature = 'app:clear-vacation-tables {--force : Force the operation to run without confirmation}';
    protected $description = 'Truncates tables related to vacations module: detalles, solicitudes, saldos, and periodos.';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('¿Estás SEGURO de que quieres borrar TODAS las vacaciones, solicitudes y saldos? Esta acción no se puede deshacer.')) {
            $this->info('Operación cancelada.');
            return;
        }

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->info('Truncating DetalleSolicitud...');
        \App\Models\DetalleSolicitud::truncate();

        $this->info('Truncating SolicitudVacaciones...');
        \App\Models\SolicitudVacaciones::truncate();

        $this->info('Truncating SaldoVacaciones...');
        \App\Models\SaldoVacaciones::truncate();

        $this->info('Truncating PeriodoVacacional...');
        \App\Models\PeriodoVacacional::truncate();

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('¡Tablas de vacaciones limpiadas correctamente!');
    }
}
