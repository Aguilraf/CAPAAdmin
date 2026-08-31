<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class DebugExcelCommand extends Command
{
    protected $signature = 'app:debug-excel-command';
    protected $description = 'Muestra los encabezados y filas reales del archivo Excel.';

    public function handle()
    {
        $path = 'g:/Mi unidad/CAPA/DIR GEN/INGRESOS/Files/012026 XML ingresos al 31012026 - copia 3.xlsx';

        if (!file_exists($path)) {
            $this->error("El archivo no existe en: $path");
            return 1;
        }

        $sheets = Excel::toArray(new class implements ToCollection {
            public function collection(Collection $collection) {}
        }, $path);

        $this->info("=== HOJAS DETECTADAS ===");
        $this->line(count($sheets));

        $data = $sheets[0];
        $this->info("=== TOTAL DE FILAS EN HOJA 1 ===");
        $this->line(count($data));

        $this->info("=== PRIMERA FILA ===");
        print_r($data[0]);

        $this->info("=== SEGUNDA FILA ===");
        print_r($data[1] ?? 'No hay segunda fila');

        return 0;
    }
}

