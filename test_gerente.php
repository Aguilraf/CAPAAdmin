<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$emp = \App\Models\Empleado::first();
echo "Empleado encontrado: {$emp->nombre}\n";
echo "es_gerente actual: " . ($emp->es_gerente ? 'true' : 'false') . "\n\n";

$emp->es_gerente = true;
$emp->save();

$emp = $emp->fresh();
echo "Después de guardar:\n";
echo "es_gerente: " . ($emp->es_gerente ? 'true' : 'false') . "\n";
