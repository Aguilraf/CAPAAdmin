<?php

use App\Models\Community;
use App\Models\Firefighter;
use App\Models\Capture;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "===========================================\n";
echo "🔄 IMPORTACIÓN INTELIGENTE DE BOMBEROS\n";
echo "===========================================\n\n";

$sqlFile = 'c:\Users\aguil\OneDrive\Documentos\firefighters_app\bomberos_3.sql';

if (!file_exists($sqlFile)) {
    die("❌ Error: No se encontró el archivo SQL en: $sqlFile\n");
}

$content = file_get_contents($sqlFile);

// ==========================================
// PASO 1: Mapear Comunidades
// ==========================================
echo "1️⃣  Procesando Comunidades...\n";
$communityMap = []; // old_id => new_id

// Extraer INSERTs de communities
// INSERT INTO `communities` (`id`, `name`, ...) VALUES (1, 'Noname', ...), (2, 'Other', ...);
if (preg_match('/INSERT INTO `communities`[^V]+VALUES\s+(.*?);/s', $content, $matches)) {
    $valuesString = $matches[1];
    $records = parseSqlValues($valuesString);

    foreach ($records as $record) {
        // Asumiendo estructura: (id, name, created_at, updated_at) o similar
        // El regex debe ser flexible. Vamos a limpiar el record y splitear por comas respetando comillas
        $values = str_getcsv($record, ",", "'");

        $oldId = $values[0]; // ID es el primero
        $name = $values[1]; // Name es el segundo

        // Buscar si existe por nombre
        $exists = Community::where('name', $name)->first();

        if ($exists) {
            $communityMap[$oldId] = $exists->id;
            // echo "   ✓ Mapeado: '$name' ($oldId -> {$exists->id})\n";
        } else {
            // Crear nuevo
            $new = Community::create(['name' => $name]);
            $communityMap[$oldId] = $new->id;
            echo "   + Creado: '$name' (Nuevo ID: {$new->id})\n";
        }
    }
}
echo "   ✅ " . count($communityMap) . " comunidades mapeadas.\n\n";

// ==========================================
// PASO 2: Mapear Bomberos
// ==========================================
echo "2️⃣  Procesando Bomberos...\n";
$firefighterMap = []; // old_id => new_id

if (preg_match('/INSERT INTO `firefighters`[^V]+VALUES\s+(.*?);/s', $content, $matches)) {
    $valuesString = $matches[1];
    $records = parseSqlValues($valuesString);

    foreach ($records as $record) {
        $values = str_getcsv($record, ",", "'");

        $oldId = $values[0];
        $name = $values[1];

        // Buscar si existe
        $exists = Firefighter::where('name', $name)->first();

        if ($exists) {
            $firefighterMap[$oldId] = $exists->id;
        } else {
            // Crear
            $new = Firefighter::create([
                'name' => $name,
                'community_id' => $communityMap[$values[2]] ?? null // Asumiendo community_id es 3ro
            ]);
            $firefighterMap[$oldId] = $new->id;
            echo "   + Creado: '$name' (Nuevo ID: {$new->id})\n";
        }
    }
}
echo "   ✅ " . count($firefighterMap) . " bomberos mapeados.\n\n";

// ==========================================
// PASO 3: Importar Capturas
// ==========================================
echo "3️⃣  Importando Capturas...\n";

// Extraer REPLACE INTO captures (o INSERT)
if (preg_match('/(?:REPLACE|INSERT) INTO `captures`[^V]+VALUES\s+(.*?);/s', $content, $matches)) {
    $valuesString = $matches[1];
    $records = parseSqlValues($valuesString);

    $imported = 0;
    $skipped = 0;

    foreach ($records as $record) {
        $values = str_getcsv($record, ",", "'");

        // Estructura SQL: 
        // id, date, year, community_id, firefighter_id, subtotal, commission, total, req_num, assign_date, round_comm, round_tot

        $oldCommId = $values[3];
        $oldFireId = $values[4];

        $newCommId = $communityMap[$oldCommId] ?? null;
        $newFireId = $firefighterMap[$oldFireId] ?? null;

        if (!$newCommId || !$newFireId) {
            $skipped++;
            continue;
        }

        // Crear captura
        try {
            Capture::create([
                // 'id' => auto-increment, no usar old id
                'date' => $values[1],
                'year' => $values[2],
                'community_id' => $newCommId,
                'firefighter_id' => $newFireId,
                'subtotal' => $values[5],
                'commission' => $values[6],
                'total' => $values[7],
                'requirement_number' => $values[8] === 'NULL' ? null : $values[8],
                'assignment_date' => $values[9] === 'NULL' ? null : $values[9],
                'rounding_commission' => $values[10] ?? 0,
                'rounding_total' => $values[11] ?? 0,
                'requirement_type' => 'bomberos' // Default
            ]);
            $imported++;

            if ($imported % 50 == 0)
                echo "   ... $imported importados\n";

        } catch (\Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }
    }

    echo "\n   ✅ Importación finalizada.\n";
    echo "   Total importados: $imported\n";
    echo "   Saltados (falta relación): $skipped\n";

} else {
    echo "❌ No se encontraron capturas en el SQL.\n";
}


// Helper para dividir los VALUES (...), (...) correctamente
function parseSqlValues($string)
{
    $records = [];
    $current = '';
    $inParen = 0;
    $inString = false;

    for ($i = 0; $i < strlen($string); $i++) {
        $char = $string[$i];

        if ($char == "'" && ($i == 0 || $string[$i - 1] != '\\')) {
            $inString = !$inString;
        }

        if (!$inString) {
            if ($char == '(' && $inParen == 0) {
                $inParen++;
                $current = '';
                continue;
            }
            if ($char == ')' && $inParen == 1) {
                $inParen--;
                $records[] = $current;
                continue;
            }
            if ($char == ',' && $inParen == 0) {
                continue; // Separador entre grupos
            }
        }

        if ($inParen > 0) {
            $current .= $char;
        }
    }
    return $records;
}
