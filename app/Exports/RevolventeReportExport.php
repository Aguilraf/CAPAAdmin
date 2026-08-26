<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RevolventeReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $items;
    protected $requirement;

    public function __construct($items, $requirement)
    {
        $this->items = $items;
        $this->requirement = $requirement;
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        return [
            'Folio Factura',
            'UUID (Folio Fiscal)',
            'Fecha Factura',
            'RFC Proveedor',
            'Nombre Proveedor',
            'Descripción',
            'Partida',
            'Subtotal',
            'IVA',
            'Descuento',
            'IEPS',
            'Ret. ISR',
            'Ret. IVA',
            'Total',
        ];
    }

    public function map($item): array
    {
        return [
            $item->invoice_folio,
            $item->uuid,
            $item->invoice_date ? $item->invoice_date->format('d/m/Y') : '',
            $item->provider_rfc,
            $item->provider_name,
            $item->description,
            $item->partida ? $item->partida->codigo : '',
            $item->invoice_subtotal,
            $item->invoice_iva,
            $item->invoice_discount,
            $item->invoice_ieps,
            $item->invoice_retention_isr,
            $item->invoice_retention_iva,
            $item->invoice_total,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
