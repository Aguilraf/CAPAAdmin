<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceImportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $prefix = $request->query('prefix');
        
        $invoices = Invoice::query()
            ->when($prefix, function ($query, $prefix) {
                $query->where('numero_factura', 'like', $prefix . '%');
            })
            ->orderBy('fecha', 'desc')
            ->get();

        $prefixes = Invoice::selectRaw('LEFT(numero_factura, 1) as prefix')
            ->distinct()
            ->pluck('prefix')
            ->filter()
            ->values();

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'prefixes' => $prefixes,
            'selectedPrefix' => $prefix
        ]);
    }

    public function upload(Request $request, InvoiceImportService $service)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $results = $service->import($request->file('file'));

        return back()->with('success', "Importación completada. Importados: {$results['imported']}, Duplicados: {$results['duplicates']}.");
    }

    public function downloadTemplate()
    {
        $headers = [
            'Folio Fiscal',
            'RFC Emisor',
            'RFC Receptor',
            'Fact',
            'Fecha Emision',
            'Imp Pagado',
            'Docto Relac',
            'S Insoluto'
        ];

        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            // Agregar UTF-8 BOM para soporte correcto de acentos y caracteres especiales en Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers, ',');
            fclose($file);
        };

        return response()->streamDownload($callback, 'plantilla_facturas_y_complementos.csv', [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT',
        ]);
    }

}
