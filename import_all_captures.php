<?php

use Illuminate\Support\Facades\DB;
use App\Models\Capture;
use App\Models\Community;
use App\Models\Firefighter;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "===========================================\n";
echo "IMPORTANDO DATOS HISTÓRICOS DESDE SQL\n";
echo "===========================================\n\n";

// Leer el archivo SQL
$sqlFile = 'c:\Users\aguil\OneDrive\Documentos\firefighters_app\bomberos_3.sql';

if (!file_exists($sqlFile)) {
    die("❌ Error: No se encontró el archivo SQL en: $sqlFile\n");
}

$content = file_get_contents($sqlFile);

// Extraer los INSERT de captures
preg_match('/REPLACE INTO `captures`[^V]+VALUES\s+(.*?);/s', $content, $matches);

if (!isset($matches[1])) {
    die("❌ Error: No se encontraron datos de captures en el archivo SQL\n");
}

$valuesString = $matches[1];

// Procesar cada registro
$totalRecords = 0;
$importedRecords = 0;
$skippedRecords = 0;
$errors = 0;

// Dividir por registros (cada uno empieza con paréntesis)
$records = [];
$currentRecord = '';
$inString = false;
$parenCount = 0;

for ($i = 0; $i < strlen($valuesString); $i++) {
    $char = $valuesString[$i];

    if ($char === "'" && ($i === 0 || $valuesString[$i - 1] !== '\\')) {
        $inString = !$inString;
    }

    if (!$inString) {
        if ($char === '(') {
            $parenCount++;
            if ($parenCount === 1 && !empty($currentRecord)) {
                $records[] = trim($currentRecord);
                $currentRecord = '';
            }
        } elseif ($char === ')') {
            $parenCount--;
        }
    }

    $currentRecord .= $char;
}

if (!empty($currentRecord)) {
    $records[] = trim($currentRecord);
}

echo "📊 Total de registros encontrados: " . count($records) . "\n\n";

foreach ($records as $record) {
    $totalRecords++;

    // Extraer valores del registro
    if (preg_match('/\((\d+),\s*\'([^\']+)\',\s*(\d+),\s*(\d+),\s*(\d+),\s*([^,]+),\s*([^,]+),\s*([^,]+),\s*(?:\'([^\']*)\')?,\s*(?:\'([^\']*)\'|NULL),\s*([^,]+),\s*([^,]+),/', $record, $values)) {

        $oldId = $values[1];
        $date = $values[2];
        $year = $values[3];
        $communityId = $values[4];
        $firefighterId = $values[5];
        $subtotal = floatval($values[6]);
        $commission = floatval($values[7]);
        $total = floatval($values[8]);
        $requirementNumber = isset($values[9]) ? trim($values[9]) : null;
        $assignmentDate = isset($values[10]) && $values[10] !== 'NULL' ? trim($values[10]) : null;
        $roundingCommission = isset($values[11]) ? floatval($values[11]) : 0.00;
        $roundingTotal = isset($values[12]) ? floatval($values[12]) : 0.00;

        try {
            // Verificar si la comunidad existe
            $community = Community::find($communityId);
            if (!$community) {
                echo "⚠️  Registro #$oldId: Comunidad ID $communityId no existe, saltando...\n";
                $skippedRecords++;
                continue;
            }

            // Verificar si el bombero existe
            $firefighter = Firefighter::find($firefighterId);
            if (!$firefighter) {
                echo "⚠️  Registro #$oldId: Bombero ID $firefighterId no existe, saltando...\n";
                $skippedRecords++;
                continue;
            }

            // Crear nuevo registro (sin usar el ID original para evitar conflictos)
            $capture = Capture::create([
                'date' => $date,
                'year' => $year,
                'community_id' => $communityId,
                'firefighter_id' => $firefighterId,
                'subtotal' => $subtotal,
                'commission' => $commission,
                'total' => $total,
                'requirement_number' => $requirementNumber,
                'assignment_date' => $assignmentDate,
                'rounding_commission' => $roundingCommission,
                'rounding_total' => $roundingTotal,
            ]);

            $importedRecords++;

            if ($importedRecords % 50 === 0) {
                echo "✓ Importados: $importedRecords registros...\n";
            }

        } catch (\Exception $e) {
            $errors++;
            echo "❌ Error en registro #$oldId: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⚠️  No se pudo parsear registro #$totalRecords\n";
        $skippedRecords++;
    }
}

echo "\n===========================================\n";
echo "RESUMEN DE IMPORTACIÓN\n";
echo "===========================================\n";
echo "Total procesados:     $totalRecords\n";
echo "✓ Importados:         $importedRecords\n";
echo "⚠️  Saltados:          $skippedRecords\n";
echo "❌ Errores:            $errors\n";
echo "===========================================\n\n";

// Verificar requerimientos únicos
$requirements = Capture::select('year', 'requirement_number')
    ->whereNotNull('requirement_number')
    ->distinct()
    ->orderBy('year', 'desc')
    ->orderBy('requirement_number', 'desc')
    ->get();

echo "📋 Requerimientos disponibles: " . $requirements->count() . "\n";
foreach ($requirements->take(10) as $req) {
    echo "   - Año: {$req->year}, Requerimiento: {$req->requirement_number}\n";
}

if ($requirements->count() > 10) {
    echo "   ... y " . ($requirements->count() - 10) . " más\n";
}

echo "\n✅ Importación completada!\n";
