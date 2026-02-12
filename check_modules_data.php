<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICANDO DATOS NECESARIOS PARA REPORTES Y BOMBEROS ===\n\n";

// 1. Verificar configuración de bomberos
echo "1. FIREFIGHTER SETTINGS\n";
$firefighterSettings = DB::table('firefighter_settings')->count();
echo "   Registros: $firefighterSettings\n";

if ($firefighterSettings == 0) {
    echo "   ⚠️ No hay configuración de bomberos\n";
    echo "   Creando configuración por defecto...\n";

    DB::table('firefighter_settings')->insert([
        'year' => date('Y'),
        'subtotal_percentage' => 0.90,
        'commission_percentage' => 0.10,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    echo "   ✓ Configuración creada\n";
}

// 2. Verificar comunidades
echo "\n2. COMMUNITIES\n";
$communities = DB::table('communities')->count();
echo "   Registros: $communities\n";

if ($communities == 0) {
    echo "   ⚠️ No hay comunidades registradas\n";
}

// 3. Verificar bomberos
echo "\n3. FIREFIGHTERS\n";
$firefighters = DB::table('firefighters')->count();
echo "   Registros: $firefighters\n";

if ($firefighters == 0) {
    echo "   ⚠️ No hay bomberos registrados\n";
}

// 4. Verificar capturas
echo "\n4. CAPTURES\n";
$captures = DB::table('captures')->count();
echo "   Registros: $captures\n";

if ($captures == 0) {
    echo "   ⚠️ No hay capturas registradas\n";
}

// 5. Verificar requirements para reportes
echo "\n5. REQUIREMENTS (para reportes)\n";
$requirements = DB::table('requirements')->count();
echo "   Registros: $requirements\n";

if ($requirements == 0) {
    echo "   ⚠️ No hay requerimientos registrados\n";
}

echo "\n=== RESUMEN ===\n";
echo "Para que Reportes funcione necesitas:\n";
echo "  - Requerimientos creados\n";
echo "  - Capturas asociadas\n";
echo "\nPara que Bomberos funcione necesitas:\n";
echo "  - Configuración de bomberos (✓ creada)\n";
echo "  - Comunidades registradas\n";
echo "  - Bomberos registrados\n";
echo "  - Capturas de bomberos\n";

echo "\n✅ Verificación completada\n";
