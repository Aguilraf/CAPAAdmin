<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== VERIFICACIÓN DE TABLAS Y DATOS ===\n\n";

// Obtener todas las tablas
$tables = DB::select('SHOW TABLES');
$dbName = env('DB_DATABASE');

$totalTables = 0;
$tablesWithData = 0;
$totalRecords = 0;

foreach ($tables as $table) {
    $tableName = $table->{"Tables_in_$dbName"};
    $totalTables++;

    try {
        $count = DB::table($tableName)->count();
        $totalRecords += $count;

        if ($count > 0) {
            $tablesWithData++;
            echo "✓ $tableName: $count registros\n";
        } else {
            echo "✗ $tableName: VACÍA\n";
        }
    } catch (\Exception $e) {
        echo "⚠ $tableName: ERROR - {$e->getMessage()}\n";
    }
}

echo "\n=== RESUMEN ===\n";
echo "Total de tablas: $totalTables\n";
echo "Tablas con datos: $tablesWithData\n";
echo "Tablas vacías: " . ($totalTables - $tablesWithData) . "\n";
echo "Total de registros: $totalRecords\n";
