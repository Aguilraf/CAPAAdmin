<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== VERIFICACIÓN DE INTEGRIDAD DEL SISTEMA ===\n\n";

// 1. Verificar estructura de travel_allowance_rates
echo "1. TABLA TRAVEL_ALLOWANCE_RATES\n";
echo "   Existe: " . (Schema::hasTable('travel_allowance_rates') ? '✓ SÍ' : '✗ NO') . "\n";

if (Schema::hasTable('travel_allowance_rates')) {
    $columns = Schema::getColumnListing('travel_allowance_rates');
    echo "   Columnas (" . count($columns) . "): " . implode(', ', $columns) . "\n";

    // Verificar columnas críticas
    $requiredColumns = ['partida_id', 'cargo', 'nivel', 'zona_1_amount', 'zona_2_amount', 'rate_type', 'year', 'active'];
    $missing = array_diff($requiredColumns, $columns);

    if (empty($missing)) {
        echo "   Estructura: ✓ COMPLETA\n";
    } else {
        echo "   Estructura: ✗ FALTAN: " . implode(', ', $missing) . "\n";
    }
}

// 2. Verificar tablas críticas
echo "\n2. TABLAS CRÍTICAS\n";
$criticalTables = [
    'users' => 'Usuarios',
    'empleados' => 'Empleados',
    'requirements' => 'Requerimientos',
    'requirement_items' => 'Items de Requerimientos',
    'capitulos' => 'Capítulos',
    'partidas' => 'Partidas',
    'travel_allowance_rates' => 'Tarifas de Viáticos',
    'vehicles' => 'Vehículos'
];

foreach ($criticalTables as $table => $name) {
    $exists = Schema::hasTable($table);
    echo "   $name: " . ($exists ? '✓' : '✗') . "\n";
}

// 3. Verificar relaciones (foreign keys)
echo "\n3. RELACIONES (FOREIGN KEYS)\n";
try {
    $fks = DB::select("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = ? 
        AND REFERENCED_TABLE_NAME IS NOT NULL
        AND TABLE_NAME = 'travel_allowance_rates'
    ", [env('DB_DATABASE')]);

    if (count($fks) > 0) {
        foreach ($fks as $fk) {
            echo "   {$fk->COLUMN_NAME} → {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
        }
    } else {
        echo "   ⚠ No se encontraron foreign keys para travel_allowance_rates\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error al verificar: " . $e->getMessage() . "\n";
}

// 4. Verificar modelos
echo "\n4. MODELOS\n";
$models = [
    'App\\Models\\User',
    'App\\Models\\Empleado',
    'App\\Models\\Requirement',
    'App\\Models\\TravelAllowanceRate',
    'App\\Models\\Capitulo',
    'App\\Models\\Partida'
];

foreach ($models as $model) {
    $exists = class_exists($model);
    $name = class_basename($model);
    echo "   $name: " . ($exists ? '✓' : '✗') . "\n";
}

// 5. Verificar controladores
echo "\n5. CONTROLADORES\n";
$controllers = [
    'App\\Http\\Controllers\\RequirementController',
    'App\\Http\\Controllers\\TravelAllowanceRateController',
    'App\\Http\\Controllers\\EmpleadoController'
];

foreach ($controllers as $controller) {
    $exists = class_exists($controller);
    $name = class_basename($controller);
    echo "   $name: " . ($exists ? '✓' : '✗') . "\n";
}

// 6. Verificar componentes React
echo "\n6. COMPONENTES REACT\n";
$components = [
    'resources/js/Pages/TravelAllowanceRates/Index.jsx',
    'resources/js/Pages/TravelAllowanceRates/Create.jsx',
    'resources/js/Pages/TravelAllowanceRates/Edit.jsx',
    'resources/js/Pages/TravelAllowanceRates/Form.jsx',
    'resources/js/Pages/Requirements/Partials/ViaticosForm.jsx'
];

foreach ($components as $component) {
    $path = __DIR__ . '/' . $component;
    $exists = file_exists($path);
    $name = basename($component);
    echo "   $name: " . ($exists ? '✓' : '✗') . "\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
