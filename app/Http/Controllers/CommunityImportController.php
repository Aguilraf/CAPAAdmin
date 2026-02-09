<?php

namespace App\Http\Controllers;

use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommunityImportController extends Controller
{
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_comunidades.csv"',
        ];

        $columns = ['nombre', 'geolocalizacion'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // Write UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write header row
            fputcsv($file, $columns);

            // Write example row
            fputcsv($file, [
                'Ciudad de México',
                '19.4326,-99.1332'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $csvData = array_map('str_getcsv', file($path));

        // Remove BOM if present
        if (isset($csvData[0][0])) {
            $csvData[0][0] = preg_replace('/^\x{FEFF}/u', '', $csvData[0][0]);
        }

        // Skip header row
        $header = array_shift($csvData);

        $imported = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            $data = [
                'name' => isset($row[0]) ? trim($row[0]) : null,
                'geolocation' => isset($row[1]) && trim($row[1]) ? trim($row[1]) : null,
            ];

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'geolocation' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $errors[] = "Fila {$rowNumber}: " . implode(', ', $validator->errors()->all());
                continue;
            }

            try {
                Community::create($data);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Fila {$rowNumber}: Error al crear - " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'message' => $imported > 0
                ? "Se importaron {$imported} comunidad(es) exitosamente"
                : "No se importó ninguna comunidad"
        ]);
    }
}
