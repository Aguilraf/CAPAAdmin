<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== ANÁLISIS DE DUPLICACIONES Y REDUNDANCIAS ===\n\n";

// 1. Analizar tablas similares
echo "1. TABLAS RELACIONADAS CON VIÁTICOS/TRAVEL\n";
$travelTables = [];
$allTables = DB::select('SHOW TABLES');
$dbName = env('DB_DATABASE');

foreach ($allTables as $table) {
    $tableName = $table->{"Tables_in_$dbName"};
    if (stripos($tableName, 'travel') !== false || stripos($tableName, 'viatico') !== false) {
        $travelTables[] = $tableName;
        $columns = Schema::getColumnListing($tableName);
        echo "   $tableName (" . count($columns) . " columnas)\n";
        echo "      Columnas: " . implode(', ', $columns) . "\n\n";
    }
}

// 2. Analizar tablas de empleados
echo "2. TABLAS RELACIONADAS CON EMPLEADOS\n";
foreach ($allTables as $table) {
    $tableName = $table->{"Tables_in_$dbName"};
    if (stripos($tableName, 'empleado') !== false || stripos($tableName, 'employee') !== false) {
        $columns = Schema::getColumnListing($tableName);
        echo "   $tableName (" . count($columns) . " columnas)\n";
        echo "      Columnas: " . implode(', ', $columns) . "\n\n";
    }
}

// 3. Analizar tablas de configuración
echo "3. TABLAS DE CONFIGURACIÓN\n";
foreach ($allTables as $table) {
    $tableName = $table->{"Tables_in_$dbName"};
    if (stripos($tableName, 'setting') !== false || stripos($tableName, 'config') !== false) {
        $columns = Schema::getColumnListing($tableName);
        echo "   $tableName (" . count($columns) . " columnas)\n";
        echo "      Columnas: " . implode(', ', $columns) . "\n\n";
    }
}

// 4. Buscar columnas duplicadas en tabla empleados
echo "4. ANÁLISIS DE COLUMNAS EN TABLA EMPLEADOS\n";
if (Schema::hasTable('empleados')) {
    $columns = Schema::getColumnListing('empleados');
    echo "   Total de columnas: " . count($columns) . "\n";
    echo "   Columnas: " . implode(', ', $columns) . "\n\n";

    // Buscar posibles duplicaciones
    $possibleDuplicates = [];
    foreach ($columns as $col) {
        if (
            stripos($col, 'cuenta') !== false ||
            stripos($col, 'clabe') !== false ||
            stripos($col, 'banco') !== false
        ) {
            $possibleDuplicates[] = $col;
        }
    }

    if (!empty($possibleDuplicates)) {
        echo "   Columnas relacionadas con cuentas bancarias:\n";
        foreach ($possibleDuplicates as $col) {
            echo "      - $col\n";
        }
        echo "\n";
    }
}

// 5. Analizar tablas de requirements
echo "5. TABLAS RELACIONADAS CON REQUIREMENTS\n";
foreach ($allTables as $table) {
    $tableName = $table->{"Tables_in_$dbName"};
    if (stripos($tableName, 'requirement') !== false || stripos($tableName, 'item') !== false) {
        $columns = Schema::getColumnListing($tableName);
        echo "   $tableName (" . count($columns) . " columnas)\n";
        echo "      Columnas: " . implode(', ', $columns) . "\n\n";
    }
}

// 6. Resumen de todas las tablas
echo "6. RESUMEN GENERAL\n";
echo "   Total de tablas: " . count($allTables) . "\n\n";

$tablesByCategory = [
    'Sistema' => ['migrations', 'cache', 'sessions', 'jobs', 'failed_jobs', 'password_reset_tokens'],
    'Permisos' => ['permissions', 'roles', 'model_has_permissions', 'model_has_roles', 'role_has_permissions'],
    'Usuarios' => ['users'],
    'Empleados' => ['empleados'],
    'Presupuesto' => ['capitulos', 'partidas'],
    'Requerimientos' => ['requirements', 'requirement_items'],
    'Viáticos/Travel' => [],
    'Bomberos' => ['firefighters', 'firefighter_settings', 'captures', 'communities', 'cfe_receipts'],
    'Otros' => []
];

// Clasificar tablas travel
foreach ($allTables as $table) {
    $tableName = $table->{"Tables_in_$dbName"};
    if (stripos($tableName, 'travel') !== false || stripos($tableName, 'viatico') !== false) {
        $tablesByCategory['Viáticos/Travel'][] = $tableName;
    }
}

foreach ($tablesByCategory as $category => $tables) {
    if (!empty($tables)) {
        echo "   $category: " . count($tables) . " tablas\n";
        foreach ($tables as $t) {
            echo "      - $t\n";
        }
    }
}

echo "\n=== ANÁLISIS COMPLETADO ===\n";
