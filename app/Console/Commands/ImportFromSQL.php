<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportFromSQL extends Command
{
    protected $signature = 'firefighters:import-sql {file}';
    protected $description = 'Importar datos desde archivo SQL';

    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("❌ Archivo no encontrado: {$file}");
            return Command::FAILURE;
        }

        $this->info("📂 Leyendo archivo SQL...");
        $sql = file_get_contents($file);

        $this->info("📊 Ejecutando importación...");

        try {
            // Deshabilitar verificación de claves foráneas temporalmente
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Ejecutar el SQL
            DB::unprepared($sql);

            // Rehabilitar verificación de claves foráneas
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->newLine();
            $this->info("🎉 Importación completada exitosamente!");

            // Mostrar resumen
            $communities = DB::table('communities')->count();
            $firefighters = DB::table('firefighters')->count();
            $captures = DB::table('captures')->count();

            $this->newLine();
            $this->table(
                ['Tabla', 'Registros'],
                [
                    ['Comunidades', $communities],
                    ['Bomberos', $firefighters],
                    ['Capturas', $captures],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error("❌ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
