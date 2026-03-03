<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Empleado;

$e = Empleado::find(55);
echo "ID: {$e->id}\n";
echo "Name: {$e->nombre}\n";
echo "Puesto: {$e->puesto}\n";
echo "Puesto Hex: " . bin2hex($e->puesto) . "\n";
