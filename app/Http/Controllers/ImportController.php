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
}
