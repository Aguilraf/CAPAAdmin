<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "===========================================\n";
echo "IMPORTANDO DATOS DESDE SQL\n";
echo "===========================================\n\n";

$sqlFile = 'c:\Users\aguil\OneDrive\Documentos\firefighters_app\bomberos_3.sql';

// Obtener el ID máximo actual para evitar conflictos
$maxId = DB::table('captures')->max('id') ?? 0;
echo "📊 ID máximo actual en captures: $maxId\n";

// Leer solo la sección de captures del SQL
$content = file_get_contents($sqlFile);

// Extraer solo los datos de captures
if (preg_match('/REPLACE INTO `captures`.*?VALUES\s+(.*?);/s', $content, $matches)) {
    $insertData = $matches[1];

    // Reemplazar REPLACE con INSERT IGNORE para no sobrescribir
    // Y ajustar los IDs para que no haya conflictos

    // Crear tabla temporal
    DB::statement('DROP TABLE IF EXISTS temp_captures');
    DB::statement('CREATE TABLE temp_captures LIKE captures');

    echo "📥 Insertando datos en tabla temporal...\n";

    // Insertar en tabla temporal
    try {
        DB::statement("INSERT INTO temp_captures VALUES $insertData");
        $tempCount = DB::table('temp_captures')->count();
        echo "✓ Datos insertados en tabla temporal: $tempCount registros\n\n";

        // Copiar de temp a captures, incrementando los IDs
        echo "📤 Copiando a tabla principal...\n";

        $imported = 0;
        $skipped = 0;

        DB::table('temp_captures')->orderBy('id')->chunk(100, function ($records) use (&$imported, &$skipped) {
            foreach ($records as $record) {
                try {
                    // Verificar que existan la comunidad y el bombero
                    $communityExists = DB::table('communities')->where('id', $record->community_id)->exists();
                    $firefighterExists = DB::table('firefighters')->where('id', $record->firefighter_id)->exists();

                    if (!$communityExists || !$firefighterExists) {
                        $skipped++;
                        continue;
                    }

                    // Insertar sin el ID (auto-increment se encargará)
                    DB::table('captures')->insert([
                        'date' => $record->date,
                        'year' => $record->year,
                        'community_id' => $record->community_id,
                        'firefighter_id' => $record->firefighter_id,
                        'subtotal' => $record->subtotal,
                        'commission' => $record->commission,
                        'total' => $record->total,
                        'requirement_number' => $record->requirement_number,
                        'assignment_date' => $record->assignment_date,
                        'rounding_commission' => $record->rounding_commission,
                        'rounding_total' => $record->rounding_total,
                        'created_at' => $record->created_at,
                        'updated_at' => $record->updated_at,
                    ]);

                    $imported++;

                    if ($imported % 50 === 0) {
                        echo "  ✓ Importados: $imported...\n";
                    }
                } catch (\Exception $e) {
                    $skipped++;
                }
            }
        });

        // Limpiar tabla temporal
        DB::statement('DROP TABLE temp_captures');

        echo "\n===========================================\n";
        echo "RESUMEN\n";
        echo "===========================================\n";
        echo "✓ Importados:  $imported\n";
        echo "⚠️  Saltados:   $skipped\n";
        echo "===========================================\n\n";

        // Mostrar requerimientos
        $requirements = DB::table('captures')
            ->select('year', 'requirement_number')
            ->whereNotNull('requirement_number')
            ->distinct()
            ->orderBy('year', 'desc')
            ->orderBy('requirement_number', 'desc')
            ->get();

        echo "📋 Requerimientos disponibles: " . count($requirements) . "\n";
        foreach ($requirements as $i => $req) {
            if ($i < 15) {
                echo "   - Año: {$req->year}, Req: {$req->requirement_number}\n";
            }
        }

        if (count($requirements) > 15) {
            echo "   ... y " . (count($requirements) - 15) . " más\n";
        }

        echo "\n✅ Importación completada!\n";

    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        DB::statement('DROP TABLE IF EXISTS temp_captures');
    }

} else {
    echo "❌ No se encontraron datos de captures en el SQL\n";
}
