<?php

use Illuminate\Support\Facades\DB;

// Conectar a la base de datos
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Importando datos de requerimientos desde SQL...\n\n";

// Leer el archivo SQL
$sqlFile = 'c:\Users\aguil\OneDrive\Documentos\firefighters_app\bomberos_3.sql';
$content = file_get_contents($sqlFile);

// Extraer solo los INSERT de captures que tienen requirement_number
preg_match('/REPLACE INTO `captures`.*?VALUES\s+(.*?);/s', $content, $matches);

if (!isset($matches[1])) {
    die("No se encontraron datos de captures en el archivo SQL\n");
}

$valuesString = $matches[1];

// Contar registros con requirement_number
$totalRecords = 0;
$importedRecords = 0;

// Procesar cada línea de valores
$lines = explode("\n", $valuesString);
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || !str_starts_with($line, '(')) {
        continue;
    }

    // Extraer valores usando regex
    if (preg_match('/\((\d+),\s*\'([^\']+)\',\s*(\d+),\s*(\d+),\s*(\d+),\s*([^,]+),\s*([^,]+),\s*([^,]+),\s*\'?([^\',]*?)\'?,/', $line, $values)) {
        $totalRecords++;

        $id = $values[1];
        $date = $values[2];
        $year = $values[3];
        $communityId = $values[4];
        $firefighterId = $values[5];
        $requirementNumber = trim($values[9], "'");

        // Solo importar si tiene requirement_number
        if (!empty($requirementNumber) && $requirementNumber !== 'NULL') {
            try {
                // Actualizar registros existentes que coincidan por fecha, año y comunidad
                $updated = DB::table('captures')
                    ->where('date', $date)
                    ->where('year', $year)
                    ->where('community_id', $communityId)
                    ->update(['requirement_number' => $requirementNumber]);

                if ($updated > 0) {
                    $importedRecords += $updated;
                    echo "✓ Actualizado: Fecha=$date, Año=$year, Comunidad=$communityId -> Req=$requirementNumber ($updated registros)\n";
                }
            } catch (\Exception $e) {
                echo "✗ Error en registro ID $id: " . $e->getMessage() . "\n";
            }
        }
    }
}

echo "\n========================================\n";
echo "Total de registros procesados: $totalRecords\n";
echo "Registros actualizados: $importedRecords\n";
echo "========================================\n";
