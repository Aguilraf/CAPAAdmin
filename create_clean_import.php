<?php

// Script para generar SQL limpio sin conflictos
// Solo importa datos de negocio, omite tablas del sistema

$backupFile = __DIR__ . '/u553580668_admincapa_1.sql';
$outputFile = __DIR__ . '/clean_import.sql';

echo "Generando archivo SQL limpio...\n";

// Tablas del sistema que NO deben importarse
$skipTables = [
    'cache',
    'cache_locks',
    'migrations',
    'sessions',
    'password_reset_tokens',
    'failed_jobs',
    'jobs',
    'job_batches',
    'permissions',
    'roles',
    'model_has_permissions',
    'model_has_roles',
    'role_has_permissions'
];

// Leer archivo línea por línea
$lines = file($backupFile);

// Iniciar archivo de salida
$output = "-- Importación LIMPIA de datos desde backup\n";
$output .= "-- Generado: " . date('Y-m-d H:i:s') . "\n";
$output .= "-- Solo datos de negocio, omite tablas del sistema\n\n";
$output .= "SET FOREIGN_KEY_CHECKS=0;\n";
$output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$output .= "START TRANSACTION;\n\n";

$insertCount = 0;
$currentInsert = "";
$inInsert = false;
$currentTable = "";
$skipCurrent = false;

foreach ($lines as $line) {
    // Detectar inicio de INSERT
    if (preg_match('/^INSERT INTO `(\w+)`/', $line, $matches)) {
        $currentTable = $matches[1];
        $skipCurrent = in_array($currentTable, $skipTables);

        if (!$skipCurrent) {
            $inInsert = true;
            $currentInsert = $line;
        }
    }
    // Si estamos en un INSERT válido, acumular líneas
    elseif ($inInsert && !$skipCurrent) {
        $currentInsert .= $line;
    }

    // Detectar fin de INSERT (línea termina con ;)
    if ($inInsert && preg_match('/;\s*$/', $line) && !$skipCurrent) {
        $output .= "\n-- Datos para tabla: $currentTable\n";
        $output .= $currentInsert;
        $insertCount++;
        $inInsert = false;
        $currentInsert = "";
    }
}

$output .= "\nSET FOREIGN_KEY_CHECKS=1;\nCOMMIT;\n";

// Guardar archivo
file_put_contents($outputFile, $output);

echo "\n✓ Archivo generado: $outputFile\n";
echo "✓ INSERT statements incluidos: $insertCount\n";
echo "✓ Tablas del sistema omitidas: " . count($skipTables) . "\n";
echo "\nAhora puedes importar este archivo en phpMyAdmin sin errores.\n";
