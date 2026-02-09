<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CfeQueryExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $receipts;

    public function __construct($receipts)
    {
        $this->receipts = $receipts;
    }

    public function collection()
    {
        return $this->receipts;
    }

    public function headings(): array
    {
        return [
            'Año',
            'Req. #',
            'Fecha Asignación',
            'RPU',
            'Poblado',
            'Dirección',
            'Periodo Inicio',
            'Periodo Fin',
            'UUID',
            'Subtotal',
            'IVA',
            'Redondeo',
            'Total',
        ];
    }

    public function map($receipt): array
    {
        // Parse description to extract poblado and direccion
        $parts = explode(',', $receipt->description, 2);
        $poblado = trim($parts[0] ?? '');
        $direccion = trim($parts[1] ?? '');

        return [
            $receipt->requirement->year ?? '',
            $receipt->requirement->number ?? '',
            $receipt->requirement->assignment_date ? $receipt->requirement->assignment_date->format('d/m/Y') : '',
            $receipt->rpu,
            $poblado,
            $direccion,
            $receipt->period_start ? \Carbon\Carbon::parse($receipt->period_start)->format('d/m/Y') : '',
            $receipt->period_end ? \Carbon\Carbon::parse($receipt->period_end)->format('d/m/Y') : '',
            $receipt->uuid,
            number_format($receipt->subtotal, 2),
            number_format($receipt->iva, 2),
            number_format($receipt->rounding, 2),
            number_format($receipt->total, 2),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
