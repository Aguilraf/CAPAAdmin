<?php

namespace App\Http\Controllers;

use App\Models\IncomeAccount;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncomeAccountController extends Controller
{
    public function index()
    {
        return Inertia::render('IncomeAccounts/Index', [
            'accounts' => IncomeAccount::orderBy('budget_account')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'budget_account' => 'required|string|max:150',
            'accounting_account' => 'required|string|max:150',
            'concept' => 'required|string|max:255',
        ]);

        IncomeAccount::create($data);

        return redirect()->route('income-accounts.index')->with('success', 'Cuenta agregada al catálogo.');
    }

    public function downloadTemplate()
    {
        return response()->streamDownload(function () {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Cuenta presupuestal', 'Cuenta contable', 'Concepto', 'Mostrar']);
            fputcsv($file, ['43010100', '4.1.4.3.01.01', 'Agua potable', 'SI']);
            fclose($file);
        }, 'plantilla_catalogo_cuentas.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|extensions:xlsx,xls,csv|max:20480']);
        $rows = Excel::toArray([], $request->file('file'))[0] ?? [];
        if (count($rows) < 2) {
            return back()->with('error', 'El archivo no contiene cuentas para importar.');
        }

        $headers = array_map(fn ($value) => $this->header($value), array_shift($rows));
        $created = 0;
        foreach ($rows as $row) {
            $data = array_combine($headers, array_pad(array_slice($row, 0, count($headers)), count($headers), null));
            if (blank($data['cuenta_presupuestal'] ?? null) || blank($data['cuenta_contable'] ?? null) || blank($data['concepto'] ?? null)) {
                continue;
            }
            IncomeAccount::updateOrCreate(
                ['budget_account' => trim((string) $data['cuenta_presupuestal']), 'accounting_account' => trim((string) $data['cuenta_contable']), 'concept' => trim((string) $data['concepto'])],
                ['visible' => $this->toBoolean($data['mostrar'] ?? false)]
            );
            $created++;
        }

        return redirect()->route('income-accounts.index')->with('success', "Catálogo cargado: {$created} registros procesados.");
    }

    private function header(mixed $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = strtr($value, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);
        return preg_replace('/[^a-z0-9]+/u', '_', $value) ?: 'columna';
    }

    private function toBoolean(mixed $value): bool
    {
        return in_array(mb_strtolower(trim((string) $value)), ['1', 'si', 'sí', 'true', 'x', 'mostrar'], true);
    }

    public function destroy(IncomeAccount $incomeAccount)
    {
        $incomeAccount->delete();

        return redirect()->route('income-accounts.index')->with('success', 'Cuenta eliminada del catálogo.');
    }

    public function toggleVisibility(IncomeAccount $incomeAccount)
    {
        $incomeAccount->update(['visible' => !$incomeAccount->visible]);

        return back()->with('success', 'Visibilidad de la cuenta actualizada.');
    }
}
