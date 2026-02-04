<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetVacations extends Command
{
    protected $signature = 'vacations:reset';
    protected $description = 'Reset only vacation related tables (entitlements, requests, etc)';

    public function handle()
    {
        if (!$this->confirm('⚠️ ESTO BORRARÁ TODAS LAS SOLICITUDES Y SALDOS DE VACACIONES. ¿Estás seguro?', false)) {
            $this->info('Operación cancelada.');
            return 1;
        }

        $tables = [
            'request_entitlements',
            'solicitudes_vacaciones',
            'entitlements',
            'bonos_evaluacion',
        ];

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->info("✅ Tabla '$table' vaciada.");
                } else {
                    $this->warn("⚠️ Tabla '$table' no encontrada.");
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('✅ Tablas de vacaciones reiniciadas correctamente.');
            return 0;

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
