<?php

namespace App\Http\Controllers;

use App\Exports\CatalogsExport;
use App\Imports\CatalogsImport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function index()
    {
        $sheets = [
            'Empleados' => 'Empleados',
            'Puestos' => 'Puestos',
            'Organismos' => 'Organismos',
            'Proveedores' => 'Proveedores',
            'Vehiculos' => 'Vehículos (Parque Vehicular)',
            'Tarifas_Viaticos' => 'Viáticos y Pasajes',
            'Materiales' => 'Materiales',
            'Unidades_Medida' => 'Unidades de Medida',
            'Capitulos' => 'Capítulos',
            'Partidas' => 'Partidas',
            'Comunidades' => 'Comunidades (Bomberos)',
            'Bomberos' => 'Bomberos',
        ];

        return Inertia::render('Imports/Index', [
            'availableSheets' => $sheets
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'sheets' => 'nullable|array',
        ]);

        try {
            $selectedSheets = $request->input('sheets', []);

            if (count($selectedSheets) === 1) {
                // Si eligen UN SOLO catálogo, importarlo directo sin importar cómo se llame la pestaña del Excel
                $allImports = [
                    'Empleados' => new \App\Imports\EmpleadosImport(),
                    'Puestos' => new \App\Imports\PuestosImport(),
                    'Organismos' => new \App\Imports\OrganismosImport(),
                    'Proveedores' => new \App\Imports\ProvidersImport(),
                    'Vehiculos' => new \App\Imports\VehiclesImport(),
                    'Tarifas_Viaticos' => new \App\Imports\TravelAllowanceRatesImport(),
                    'Materiales' => new \App\Imports\MaterialesImport(),
                    'Unidades_Medida' => new \App\Imports\UnidadMedidaImport(),
                    'Capitulos' => new \App\Imports\CapitulosImport(),
                    'Partidas' => new \App\Imports\PartidasImport(),
                    'Comunidades' => new \App\Imports\CommunitiesImport(),
                    'Bomberos' => new \App\Imports\FirefightersImport(),
                ];
                $key = $selectedSheets[0];
                if (isset($allImports[$key])) {
                    Excel::import($allImports[$key], $request->file('file'));
                    return redirect()->back()->with('success', 'Importación de catálogos completada exitosamente.');
                }
            }

            // Comportamiento por defecto si seleccionan múltiples catálogos (requiere nombres de pestaña exactos)
            Excel::import(new CatalogsImport($selectedSheets), $request->file('file'));

            return redirect()->back()->with('success', 'Importación de catálogos completada exitosamente.');
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'out of bounds')) {
                // Extract sheet name from error if possible (format: "Your requested sheet name [Empleados] is out of bounds.")
                preg_match('/\[(.*?)\]/', $msg, $matches);
                $failedSheet = $matches[1] ?? 'seleccionada';
                return redirect()->back()->with('error', "Error al importar: No se encontró la pestaña '{$failedSheet}' en el archivo Excel. Asegúrese de que el nombre de la hoja inferior en su archivo Excel coincida exactamente con la categoría que desea importar ('{$failedSheet}') o descargue una nueva plantilla.");
            }
            return redirect()->back()->with('error', 'Error al procesar el Excel: ' . $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error inesperado al importar: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new CatalogsExport, 'Catalogos_CAPA_' . date('d-m-Y') . '.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new CatalogsExport, 'Plantilla_Catalogos_CAPA.xlsx');
    }

    public function downloadBackup()
    {
        try {
            $tables = array_map('current', \DB::select('SHOW TABLES'));
            $totalRecords = 0;
            $tableCounts = [];

            // Primera pasada para contar registros y generar el manifiesto
            foreach ($tables as $table) {
                $count = \DB::table($table)->count();
                $totalRecords += $count;
                $tableCounts[$table] = $count;
            }

            $sqlDump = "-- SIGEJMM AUTOMATIC DATABASE BACKUP\n";
            $sqlDump .= "-- Generado: " . date('Y-m-d H:i:s') . "\n";
            $sqlDump .= "-- TOTAL DE TABLAS RESPALDADAS: " . count($tables) . "\n";
            $sqlDump .= "-- TOTAL DE REGISTROS RESPALDADOS: " . $totalRecords . "\n";
            $sqlDump .= "-- MANIFIESTO DE TABLAS Y REGISTROS:\n";
            foreach ($tableCounts as $table => $count) {
                $sqlDump .= "--   - {$table}: {$count} registros\n";
            }
            $sqlDump .= "\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

            foreach ($tables as $table) {
                $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $createTableResult = \DB::select("SHOW CREATE TABLE `{$table}`");
                $createSql = $createTableResult[0]->{'Create Table'} ?? '';
                $sqlDump .= $createSql . ";\n\n";

                $rows = \DB::table($table)->get();
                if ($rows->count() > 0) {
                    $sqlDump .= "INSERT INTO `{$table}` (";
                    $columns = array_keys((array)$rows->first());
                    $sqlDump .= implode(', ', array_map(fn($col) => "`{$col}`", $columns)) . ") VALUES\n";

                    $valuesList = [];
                    foreach ($rows as $row) {
                        $rowArray = (array)$row;
                        $escapedValues = array_map(function($val) {
                            if ($val === null) return 'NULL';
                            return \DB::getPdo()->quote($val);
                        }, $rowArray);
                        $valuesList[] = "  (" . implode(', ', $escapedValues) . ")";
                    }
                    $sqlDump .= implode(",\n", $valuesList) . ";\n\n";
                }
            }

            $sqlDump .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            $filename = 'sigejmm_backup_' . date('Y-m-d_H-i-s') . '.sql';

            return response()->streamDownload(function() use ($sqlDump) {
                echo $sqlDump;
            }, $filename, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar el respaldo: ' . $e->getMessage());
        }
    }

    public function uploadBackup(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        try {
            $file = $request->file('file');
            $sqlContent = file_get_contents($file->getRealPath());

            if (!str_contains($sqlContent, 'SET FOREIGN_KEY_CHECKS')) {
                return redirect()->back()->with('error', 'El archivo no parece ser un respaldo válido de SIGEJMM.');
            }

            \DB::unprepared($sqlContent);

            // Obtener el conteo dinámico post-restauración para informarlo en la confirmación
            $tables = array_map('current', \DB::select('SHOW TABLES'));
            $totalRecords = 0;
            foreach ($tables as $table) {
                $totalRecords += \DB::table($table)->count();
            }

            return redirect()->back()->with('success', "Base de datos restaurada exitosamente. Se importaron " . count($tables) . " tablas con un total de " . number_format($totalRecords) . " registros de forma automática.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al restaurar la base de datos: ' . $e->getMessage());
        }
    }
}
