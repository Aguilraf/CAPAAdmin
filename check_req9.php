<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Capture;
use App\Models\Community;
use App\Models\Firefighter;

// Captura pendientes del día 2026-07-09
$captures = Capture::whereNull('requirement_number')
    ->whereDate('date', '2026-07-09')
    ->orderBy('commission', 'desc')
    ->with(['community', 'firefighter'])
    ->get();

echo "=== REGISTROS PENDIENTES (sin requerimiento) del 2026-07-09 ===\n";
echo "Total registros: " . $captures->count() . "\n";
echo "SUBTOTAL: " . number_format($captures->sum('subtotal'), 2) . "\n";
echo "COMISION: " . number_format($captures->sum('commission'), 2) . "\n";
echo "TOTAL:    " . number_format($captures->sum('total'), 2) . "\n";
echo "\n";

printf("%-6s %-30s %-30s %12s %12s %12s %8s %8s\n",
    "ID", "Comunidad", "Bombero", "Subtotal", "Comision", "Total", "R.Com", "R.Tot");
echo str_repeat("-", 125) . "\n";

foreach ($captures as $c) {
    printf("%-6s %-30s %-30s %12s %12s %12s %8s %8s\n",
        $c->id,
        substr($c->community?->name ?? 'N/A', 0, 28),
        substr($c->firefighter?->name ?? 'N/A', 0, 28),
        number_format($c->subtotal, 2),
        number_format($c->commission, 2),
        number_format($c->total, 2),
        number_format($c->rounding_commission, 2),
        number_format($c->rounding_total, 2)
    );
}

echo "\n=== VERIFICACIÓN: subtotal - comision vs total guardado ===\n";
$errores = 0;
foreach ($captures as $c) {
    $expected = (float)$c->subtotal - (float)$c->commission + (float)$c->rounding_total;
    $diff = abs($expected - (float)$c->total);
    if ($diff > 0.02) {
        echo "⚠ ERROR ID {$c->id} ({$c->community?->name}): subtotal={$c->subtotal} - commission={$c->commission} + rt={$c->rounding_total} = " . number_format($expected,2) . " vs total guardado={$c->total} (diff=$diff)\n";
        $errores++;
    }
}
if ($errores === 0) {
    echo "Todos los registros tienen totales consistentes.\n";
}

echo "\n=== TAMBIÉN: revisar requerimiento 9 (ya asignado) ===\n";
$req9 = Capture::where('requirement_number', '9')
    ->where('year', 2026)
    ->with(['community', 'firefighter'])
    ->orderBy('commission', 'desc')
    ->get();

echo "Registros en req 9: " . $req9->count() . "\n";
echo "SUBTOTAL: " . number_format($req9->sum('subtotal'), 2) . "\n";
echo "COMISION: " . number_format($req9->sum('commission'), 2) . "\n";
echo "TOTAL:    " . number_format($req9->sum('total'), 2) . "\n";
