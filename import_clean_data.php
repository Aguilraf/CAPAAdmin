<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== IMPORTANDO DATOS LIMPIOS ===\n\n";

$sqlFile = __DIR__ . '/clean_import.sql';

if (!file_exists($sqlFile)) {
    die("ERROR: No se encontró el archivo $sqlFile\n");
}

echo "Leyendo archivo SQL limpio...\n";
$sql = file_get_contents($sqlFile);

// Dividir en statements individuales
$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "Ejecutando " . count($statements) . " statements...\n\n";

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    $success = 0;
    $errors = 0;

    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }

        try {
            DB::statement($statement);
            $success++;

            // Mostrar progreso cada 5 statements
            if ($success % 5 == 0) {
                echo "✓ $success statements ejecutados...\n";
            }
        } catch (\Exception $e) {
            $errors++;
            echo "✗ Error en statement: " . substr($statement, 0, 50) . "...\n";
            echo "  " . $e->getMessage() . "\n";
        }
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    echo "\n=== RESUMEN ===\n";
    echo "✓ Statements exitosos: $success\n";
    echo "✗ Statements con error: $errors\n";

    // Verificar datos importados
    echo "\n=== VERIFICANDO DATOS ===\n\n";

    $tables = ['users', 'empleados', 'capitulos', 'partidas', 'requirements', 'captures', 'communities', 'firefighters'];

    foreach ($tables as $table) {
        try {
            $count = DB::table($table)->count();
            echo "$table: $count registros\n";
        } catch (\Exception $e) {
            echo "$table: ERROR\n";
        }
    }

} catch (\Exception $e) {
    echo "\n✗ ERROR FATAL:\n";
    echo $e->getMessage() . "\n";
}
