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
        ]);

        try {
            Excel::import(new ClasificadorImport, $request->file('file'));

            return redirect()->back()->with('success', 'Importación completada exitosamente. Se han cargado Capítulos y Partidas.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['file' => 'Error al importar: ' . $e->getMessage()]);
        }
    }

    public function downloadTemplate()
    {
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
