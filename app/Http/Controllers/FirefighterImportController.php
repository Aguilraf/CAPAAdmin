<?php

namespace App\Http\Controllers;

use App\Models\Firefighter;
use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FirefighterImportController extends Controller
{
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_bomberos.csv"',
        ];

        $columns = [
            'nombre',
            'comunidad_id'
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // Write UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write header row
            fputcsv($file, $columns);

            // Write example row
            fputcsv($file, [
                'Juan Pérez',
                '1'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $content = file_get_contents($path);

        // Remove UTF-8 BOM if present (safer method)
        if (strpos($content, "\xEF\xBB\xBF") === 0) {
            $content = substr($content, 3);
        }

        // Convert to UTF-8 if it seems to be something else (Windows-1252 is common)
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        // Standardize line endings
        $content = str_replace("\r\n", "\n", $content);
        $content = str_replace("\r", "\n", $content);
        $lines = explode("\n", $content);

        if (count($lines) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo parece estar vacío o no tiene el formato correcto.',
                'debug_count' => count($lines)
            ], 422);
        }

        // Clean lines and remove empty ones
        $lines = array_filter(array_map('trim', $lines));
        $header = array_shift($lines);

        // Detect delimiter: check commas vs semicolons
        $commas = substr_count($header, ',');
        $semicolons = substr_count($header, ';');
        $delimiter = ($semicolons > $commas) ? ';' : ',';

        $imported = 0;
        $errors = [];
        $rowNumber = 1; // Header was row 1

        foreach ($lines as $line) {
            $rowNumber++;
            if (empty($line))
                continue;

            $row = str_getcsv($line, $delimiter);

            // Fallback for this line if delimiter detection was wrong
            if (count($row) < 2) {
                $otherDelimiter = ($delimiter === ',') ? ';' : ',';
                $row = str_getcsv($line, $otherDelimiter);
            }

            if (count($row) < 2) {
                $errors[] = "Fila {$rowNumber}: Formato incorrecto (se esperaban 2 columnas)";
                continue;
            }

            $name = trim($row[0]);
            $communityIdRaw = trim($row[1]);

            if (empty($name) || empty($communityIdRaw)) {
                $errors[] = "Fila {$rowNumber}: Nombre o ID de comunidad vacío";
                continue;
            }

            // Extract numeric ID only
            $communityId = preg_replace('/[^0-9]/', '', $communityIdRaw);

            if (empty($communityId)) {
                $errors[] = "Fila {$rowNumber}: ID de comunidad '$communityIdRaw' no es válido (debe ser un número)";
                continue;
            }

            $community = Community::find($communityId);
            if (!$community) {
                $errors[] = "Fila {$rowNumber}: No existe comunidad con ID '$communityId'";
                continue;
            }

            try {
                Firefighter::create([
                    'name' => $name,
                    'community_id' => $communityId,
                    'active' => true,
                    'max_rounding_amount' => 0
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Fila {$rowNumber}: Error al guardar - " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'message' => $imported > 0
                ? "Se importaron $imported bombero(s) exitosamente."
                : "No se importó ningún bombero. Verifique los errores."
        ]);
    }
}
