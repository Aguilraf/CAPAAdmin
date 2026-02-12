# Script para generar SQL limpio sin conflictos
# Solo importa datos de negocio, omite tablas del sistema

$backupFile = "c:\Users\aguil\OneDrive\Documentos\CAPAAdmin\u553580668_admincapa_1.sql"
$outputFile = "c:\Users\aguil\OneDrive\Documentos\CAPAAdmin\clean_import.sql"

Write-Host "Generando archivo SQL limpio..." -ForegroundColor Cyan

# Tablas del sistema que NO deben importarse (ya tienen datos)
$skipTables = @(
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
)

# Leer el archivo SQL línea por línea
$lines = Get-Content $backupFile

# Crear archivo de salida
$output = @"
-- Importación LIMPIA de datos desde backup
-- Generado: $(Get-Date)
-- Solo datos de negocio, omite tablas del sistema

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- Limpiar tablas antes de importar
TRUNCATE TABLE empleados;
TRUNCATE TABLE capitulos;
TRUNCATE TABLE partidas;
TRUNCATE TABLE communities;
TRUNCATE TABLE firefighters;
TRUNCATE TABLE captures;
TRUNCATE TABLE materials;
TRUNCATE TABLE unidad_medidas;
TRUNCATE TABLE leyendas;
TRUNCATE TABLE requirements;
TRUNCATE TABLE requirement_items;
TRUNCATE TABLE settings;
TRUNCATE TABLE configuracion;
TRUNCATE TABLE firefighter_settings;
TRUNCATE TABLE cfe_receipts;

"@

$insertCount = 0
$currentInsert = ""
$inInsert = $false
$currentTable = ""
$skipCurrent = $false

foreach ($line in $lines) {
    # Detectar inicio de INSERT
    if ($line -match "^INSERT INTO ``(\w+)``") {
        $currentTable = $matches[1]
        $skipCurrent = $skipTables -contains $currentTable
        
        if (-not $skipCurrent) {
            $inInsert = $true
            $currentInsert = $line
        }
    }
    # Si estamos en un INSERT válido, acumular líneas
    elseif ($inInsert -and -not $skipCurrent) {
        $currentInsert += [Environment]::NewLine + $line
    }
    
    # Detectar fin de INSERT (línea termina con ;)
    if ($inInsert -and $line -match ";`$" -and -not $skipCurrent) {
        $output += [Environment]::NewLine + "-- Datos para tabla: $currentTable" + [Environment]::NewLine
        $output += $currentInsert + [Environment]::NewLine
        $insertCount++
        $inInsert = $false
        $currentInsert = ""
    }
}

$output += [Environment]::NewLine + "SET FOREIGN_KEY_CHECKS=1;" + [Environment]::NewLine + "COMMIT;" + [Environment]::NewLine

# Guardar archivo
$output | Out-File -FilePath $outputFile -Encoding UTF8

Write-Host "`n✓ Archivo generado: $outputFile" -ForegroundColor Green
Write-Host "✓ INSERT statements incluidos: $insertCount" -ForegroundColor Yellow
Write-Host "✓ Tablas del sistema omitidas: $($skipTables.Count)" -ForegroundColor Cyan
Write-Host "`nAhora puedes importar este archivo en phpMyAdmin sin errores." -ForegroundColor White
