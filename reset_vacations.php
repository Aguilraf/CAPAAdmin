<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = [
    'request_entitlements',
    'solicitudes_vacaciones',
    'entitlements',
    'evaluaciones_cuatrimestrales', // Including this as it relates to bonuses/vacation logic
];

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        DB::table($table)->truncate();
        echo "✅ Tabla '$table' vaciada.\n";
    } else {
        echo "⚠️ Tabla '$table' no encontrada.\n";
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "✅ Tablas de vacaciones reiniciadas correctamente.\n";
