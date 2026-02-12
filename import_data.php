<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== IMPORTANDO DATOS DESDE RESPALDO ===\n\n";

$sqlFile = __DIR__ . '/data_only_import.sql';

if (!file_exists($sqlFile)) {
    die("ERROR: No se encontró el archivo $sqlFile\n");
}

echo "Leyendo archivo SQL...\n";
$sql = file_get_contents($sqlFile);

echo "Ejecutando importación...\n";

try {
    // Deshabilitar verificación de claves foráneas
    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    // Ejecutar el SQL completo
    DB::unprepared($sql);

    // Rehabilitar verificación de claves foráneas
    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    echo "\n✓ Importación completada exitosamente!\n\n";

    // Verificar tablas con datos
    echo "=== VERIFICANDO DATOS IMPORTADOS ===\n\n";

    $importantTables = [
        'users',
        'empleados',
        'capitulos',
        'partidas',
        'requirements',
        'captures',
        'communities',
        'firefighters',
        'materials'
    ];

    foreach ($importantTables as $table) {
        try {
            $count = DB::table($table)->count();
            echo "$table: $count registros\n";
        } catch (\Exception $e) {
            echo "$table: ERROR - {$e->getMessage()}\n";
        }
    }

} catch (\Exception $e) {
    echo "\n✗ ERROR durante la importación:\n";
    echo $e->getMessage() . "\n";
    echo "\nDetalles:\n";
    echo $e->getTraceAsString() . "\n";
}
