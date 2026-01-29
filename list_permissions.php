<?php
// Script temporal para listar permisos de Bomberos
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PERMISOS DE BOMBEROS ===" . PHP_EOL . PHP_EOL;

$permisos = \Spatie\Permission\Models\Permission::where('name', 'like', '%bomberos%')
    ->orWhere('name', 'like', '%comunidades%')
    ->orderBy('name')
    ->get();

foreach ($permisos as $permiso) {
    echo "✓ " . $permiso->name . PHP_EOL;
}

echo PHP_EOL . "Total: " . $permisos->count() . " permisos" . PHP_EOL;
