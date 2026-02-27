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
            Excel::import(new CatalogsImport($selectedSheets), $request->file('file'));

            return redirect()->back()->with('success', 'Importación de catálogos completada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al importar: ' . $e->getMessage());
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
