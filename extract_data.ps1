# Script para importar solo datos (INSERT) del backup SQL
# Mantiene la estructura actual de las tablas

$backupFile = "c:\Users\aguil\OneDrive\Documentos\CAPAAdmin\u553580668_admincapa_1.sql"
$outputFile = "c:\Users\aguil\OneDrive\Documentos\CAPAAdmin\data_only_import.sql"

Write-Host "Extrayendo INSERT statements del backup..." -ForegroundColor Cyan

# Leer el archivo SQL línea por línea
$lines = Get-Content $backupFile

# Crear archivo de salida
$output = @"
-- Importación de datos desde backup
-- Generado: $(Get-Date)
-- IMPORTANTE: Este script solo contiene INSERT statements

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

"@

$insertCount = 0
$currentInsert = ""
$inInsert = $false

foreach ($line in $lines) {
    # Detectar inicio de INSERT
    if ($line -match "^INSERT INTO") {
        $inInsert = $true
        $currentInsert = $line
    }
    # Si estamos en un INSERT, acumular líneas
    elseif ($inInsert) {
        $currentInsert += "`n$line"
    }
    
    # Detectar fin de INSERT (línea termina con ;)
    if ($inInsert -and $line -match ";$") {
        $output += "$currentInsert`n"
        $insertCount++
        $inInsert = $false
        $currentInsert = ""
    }
}

$output += "`nSET FOREIGN_KEY_CHECKS=1;`nCOMMIT;`n"

# Guardar archivo
$output | Out-File -FilePath $outputFile -Encoding UTF8

Write-Host "Archivo generado: $outputFile" -ForegroundColor Green
Write-Host "Total de INSERT statements: $insertCount" -ForegroundColor Yellow
Write-Host "`nPara importar, ejecuta:" -ForegroundColor Cyan
Write-Host "mysql -u root -p sigejmm < data_only_import.sql" -ForegroundColor White
