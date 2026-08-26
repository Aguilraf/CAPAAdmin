<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class BankMovementController extends Controller
{
    public function index(Request $request)
    {
        $banks = Bank::where('active', true)->orderBy('name')->orderBy('account_number')->get(['id', 'name', 'account_number', 'import_template']);
        $movements = BankMovement::with('bank:id,name,account_number')
            ->when($request->filled('bank_id'), fn ($query) => $query->where('bank_id', $request->integer('bank_id')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($movementQuery) use ($request) {
                    $movementQuery->where('description', 'like', '%' . $request->search . '%')
                        ->orWhere('reference', 'like', '%' . $request->search . '%')
                        ->orWhere('movement_number', 'like', '%' . $request->search . '%');
                });
            })
            ->orderByDesc('operation_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Incomes/Index', [
            'banks' => $banks,
            'movements' => $movements,
            'filters' => $request->only(['bank_id', 'search']),
        ]);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_movimientos_estandar.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            fputcsv($file, [
                'Fecha (AAAA-MM-DD)',
                'Movimiento',
                'Descripción',
                'Referencia',
                'Abono',
                'Cargo',
                'Saldo'
            ]);

            fputcsv($file, [
                date('Y-m-d'),
                '123456',
                'EJEMPLO DE ABONO POR TRANSFERENCIA',
                'REF-001',
                '1500.00',
                '0.00',
                '1500.00'
            ]);
            
            fputcsv($file, [
                date('Y-m-d'),
                '123457',
                'EJEMPLO DE CARGO POR COMISION',
                'REF-002',
                '0.00',
                '35.50',
                '1464.50'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        $bank = Bank::findOrFail($validated['bank_id']);

        try {
            try {
                $rows = Excel::toArray([], $request->file('file'))[0] ?? [];
            } catch (\Throwable $exception) {
                if ($bank->import_template !== 'azteca') {
                    throw $exception;
                }

                $rows = $this->readAztecaRows($request->file('file')->getRealPath());
            }
            if (count($rows) < 2) {
                return back()->with('error', 'El archivo no contiene movimientos para importar.');
            }

            $headers = array_map(fn ($value) => $this->header($value), array_shift($rows));
            $created = 0;
            $skipped = 0;

            foreach ($rows as $row) {
                $data = array_combine($headers, array_pad(array_slice($row, 0, count($headers)), count($headers), null));
                
                if ($bank->import_template === 'hsbc') {
                    $movement = $this->hsbcMovement($data);
                } elseif ($bank->import_template === 'azteca') {
                    $movement = $this->aztecaMovement($data);
                } else {
                    $movement = $this->customMovement($data);
                }

                if (!$movement) {
                    $skipped++;
                    continue;
                }

                $movement['bank_id'] = $bank->id;
                $movement['source_file'] = $request->file('file')->getClientOriginalName();
                $movement['source_data'] = $data;
                $movement['fingerprint'] = hash('sha256', implode('|', [
                    $bank->id,
                    $movement['operation_date'],
                    $movement['movement_number'] ?? '',
                    $movement['reference'] ?? '',
                    $movement['credit_amount'],
                    $movement['debit_amount'],
                    $movement['description'] ?? '',
                ]));

                $record = BankMovement::firstOrCreate(['fingerprint' => $movement['fingerprint']], $movement);
                $record->wasRecentlyCreated ? $created++ : $skipped++;
            }

            return redirect()->route('incomes.index', ['bank_id' => $bank->id])
                ->with('success', "Importación terminada: {$created} movimientos nuevos y {$skipped} omitidos (duplicados o filas sin datos).");
        } catch (\Throwable $exception) {
            report($exception);
            return back()->with('error', 'No fue posible leer el archivo. Verifica que corresponda al formato configurado para este banco.');
        }
    }

    private function hsbcMovement(array $row): ?array
    {
        $date = $this->date($row['fecha_valor'] ?? $row['fecha_del_apunte'] ?? null);
        $credit = $this->amount($row['importe_de_credito'] ?? null);
        $debit = $this->amount($row['importe_del_debito'] ?? null);

        if (!$date || (!$credit && !$debit)) {
            return null;
        }

        return [
            'operation_date' => $date,
            'application_date' => $this->date($row['fecha_del_apunte'] ?? null),
            'movement_number' => $this->string($row['referencia_bancaria'] ?? null),
            'reference' => $this->string($row['referencia_de_cliente'] ?? null),
            'transaction_type' => $this->string($row['tipo_de_trn'] ?? null),
            'description' => $this->string($row['descripcion'] ?? null),
            'credit_amount' => $credit,
            'debit_amount' => $debit,
            'balance' => $this->amount($row['saldo'] ?? null, true),
        ];
    }

    private function aztecaMovement(array $row): ?array
    {
        $date = $this->date($row['fecha_de_operacion'] ?? null);
        $amount = $this->amount($row['importe'] ?? null, true);

        if (!$date || $amount === null) {
            return null;
        }

        return [
            'operation_date' => $date,
            'application_date' => $this->date($row['fecha_de_aplicacion'] ?? null),
            'movement_number' => $this->aztecaMovementNumber($row['movimiento'] ?? null),
            'reference' => null,
            'transaction_type' => $amount < 0 ? 'Cargo' : 'Abono',
            'description' => $this->string($row['concepto'] ?? null),
            'credit_amount' => max($amount, 0),
            'debit_amount' => abs(min($amount, 0)),
            'balance' => $this->amount($row['saldo'] ?? null, true),
        ];
    }

    private function header(mixed $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = strtr($value, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']);
        return preg_replace('/[^a-z0-9]+/u', '_', $value) ?: 'columna';
    }

    private function amount(mixed $value, bool $allowNegative = false): ?float
    {
        if ($value === null || $value === '') {
            return $allowNegative ? null : 0;
        }

        $normalized = str_replace([',', '$', ' '], '', (string) $value);
        $amount = (float) $normalized;
        return $allowNegative ? $amount : abs($amount);
    }

    private function date(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim((string) $value);

        try {
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('d/m/Y', $value)->toDateString();
            }

            if (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $value)) {
                return Carbon::createFromFormat('d-m-Y', $value)->toDateString();
            }

            return Carbon::parse(str_replace('/', '-', $value))->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function string(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function aztecaMovementNumber(mixed $value): ?string
    {
        $number = $this->string($value);

        return $number && ctype_digit($number)
            ? str_pad($number, 9, '0', STR_PAD_LEFT)
            : $number;
    }

    private function customMovement(array $row): ?array
    {
        // BBVA (OpenPay) specific parsing
        if (isset($row['orden'])) {
            $date = $this->date($row['fecha'] ?? null);
            $amount = $this->amount($row['total_importe'] ?? null);
            
            if (!$date || !$amount) return null;

            return [
                'operation_date' => $date,
                'application_date' => $this->date($row['fecha_de_dispersion'] ?? $row['fecha'] ?? null),
                'movement_number' => $this->string($row['orden'] ?? null),
                'reference' => $this->string($row['referencia_comercio'] ?? null),
                'transaction_type' => 'Abono',
                'description' => $this->string($row['concepto'] ?? 'Movimiento BBVA') . ' | ' . $this->string($row['titular'] ?? ''),
                'credit_amount' => $amount,
                'debit_amount' => 0,
                'balance' => null,
            ];
        }

        // Generic custom parsing
        $dateKey = $this->findKey($row, ['fecha', 'date']);
        $movKey = $this->findKey($row, ['movimiento', 'numero', 'movement']);
        $descKey = $this->findKey($row, ['descripcion', 'concepto', 'description']);
        $refKey = $this->findKey($row, ['referencia', 'reference']);
        $creditKey = $this->findKey($row, ['abono', 'credito', 'credit', 'importe_de_credito']);
        $debitKey = $this->findKey($row, ['cargo', 'debito', 'debit', 'importe_del_debito']);
        $balanceKey = $this->findKey($row, ['saldo', 'balance']);

        $date = $this->date($row[$dateKey] ?? null);
        $credit = $this->amount($row[$creditKey] ?? null);
        $debit = $this->amount($row[$debitKey] ?? null);

        if (!$date || (!$credit && !$debit)) {
            return null;
        }

        return [
            'operation_date' => $date,
            'application_date' => $date,
            'movement_number' => $this->string($row[$movKey] ?? null),
            'reference' => $this->string($row[$refKey] ?? null),
            'transaction_type' => $credit > 0 ? 'Abono' : 'Cargo',
            'description' => $this->string($row[$descKey] ?? 'Movimiento Estándar'),
            'credit_amount' => $credit,
            'debit_amount' => $debit,
            'balance' => $this->amount($row[$balanceKey] ?? null, true),
        ];
    }

    private function findKey(array $row, array $keywords): string
    {
        foreach ($row as $key => $val) {
            foreach ($keywords as $kw) {
                if (str_contains((string) $key, $kw)) {
                    return $key;
                }
            }
        }
        return array_key_first($row);
    }

    private function readAztecaRows(string $path): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('La extensión ZIP de PHP no está disponible.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('No se pudo abrir el archivo de Banco Azteca.');
        }

        try {
            $sharedStrings = [];
            $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedStringsXml !== false) {
                $xml = simplexml_load_string($sharedStringsXml);
                $xml->registerXPathNamespace('sheet', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                foreach ($xml->xpath('//sheet:si') as $item) {
                    $sharedStrings[] = trim(strip_tags($item->asXML()));
                }
            }

            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheetXml === false) {
                throw new \RuntimeException('No se encontró la hoja de movimientos de Banco Azteca.');
            }

            $sheet = simplexml_load_string($sheetXml);
            $sheet->registerXPathNamespace('sheet', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $rows = [];

            foreach ($sheet->xpath('//sheet:sheetData/sheet:row') as $xmlRow) {
                $values = [];
                foreach ($xmlRow->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main') as $cell) {
                    $attributes = $cell->attributes();
                    $value = (string) $cell->v;
                    $values[] = ((string) $attributes['t'] === 's' && $value !== '')
                        ? ($sharedStrings[(int) $value] ?? '')
                        : $value;
                }
                $rows[] = $values;
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }
}
