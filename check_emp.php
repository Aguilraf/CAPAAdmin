<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Empleado;

$empleados = Empleado::where('activo', 1)->get();
foreach ($empleados as $e) {
    if (stripos($e->puesto, 'COORDINADOR') !== false) {
        echo "ID: {$e->id} | Name: {$e->nombre} | Puesto: {$e->puesto}\n";
    }
}
