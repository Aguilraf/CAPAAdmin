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
            'file' => 'required|file|mimes:csv,txt,xlsx,xls'
        ]);

        $import = new \App\Imports\FirefightersImport();

        try {
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            foreach ($failures as $failure) {
                $import->errors[] = "Fila {$failure->row()}: " . implode(', ', $failure->errors());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage(),
                'errors' => []
            ], 500);
        }

        return response()->json([
            'success' => true,
            'imported' => $import->imported,
            'errors' => $import->errors,
            'message' => $import->imported > 0
                ? "Se importaron {$import->imported} bombero(s) exitosamente."
                : "No se importó ningún bombero. Verifique los errores."
        ]);
    }
}
