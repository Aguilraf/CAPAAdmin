<?php

namespace App\Http\Controllers;

use App\Imports\ClasificadorImport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function index()
    {
        return Inertia::render('Imports/Index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'type' => 'nullable|string|in:clasificador,vehicles',
        ]);

        $type = $request->input('type', 'clasificador');

        try {
            if ($type === 'vehicles') {
                Excel::import(new \App\Imports\VehicleImport, $request->file('file'));
                return redirect()->back()->with('success', 'Importación de Vehículos completada exitosamente.');
            } else {
                Excel::import(new ClasificadorImport, $request->file('file'));
                return redirect()->back()->with('success', 'Importación completada exitosamente. Se han cargado Capítulos y Partidas.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['file' => 'Error al importar: ' . $e->getMessage()]);
        }
    }

    public function downloadTemplate(Request $request)
    {
        $type = $request->query('type', 'clasificador');

        if ($type === 'vehicles') {
            return response()->streamDownload(function () {
                $handle = fopen('php://output', 'w');
                // Headers for Vehicle Import
                fputcsv($handle, [
                    'inventario',
                    'unidad',
                    'marca',
                    'tipo',
                    'color',
                    'modelo',
                    'serie',
                    'motor',
                    'placa',
                    'area',
                    'ubicacion',
                    'resguardante',
                    'organismo'
                ]);
                // Example data
                fputcsv($handle, [
                    'INV-001',
                    'CAMIONETA',
                    'FORD',
                    'RANGER',
                    'BLANCO',
                    '2023',
                    'FJHKS234',
                    'MTR987',
                    'ABC-123',
                    'OBRAS PUBLICAS',
                    'PATIO',
                    'JUAN PEREZ',
                    'AYUNTAMIENTO'
                ]);
                fclose($handle);
            }, 'plantilla_vehiculos.csv');
        }

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // Encabezados simples solicitados
            fputcsv($handle, ['Capitulo', 'Subcapítulo', 'Partida. Genérica', 'Partida. Específica', 'Denominación']);

            // Ejemplos
            fputcsv($handle, ['1000', '', '', '', 'SERVICIOS PERSONALES']);
            fputcsv($handle, ['1000', '1100', '113', '11301', 'Sueldos Base al Personal Permanente']);

            fclose($handle);
        }, 'layout_importacion.csv');
    }
}
