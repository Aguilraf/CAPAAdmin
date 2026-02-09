<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FirefighterQueryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $captures;

    public function __construct($captures)
    {
        $this->captures = $captures;
    }

    public function collection()
    {
        return $this->captures;
    }

    public function headings(): array
    {
        return [
            'Año',
            'Req. #',
            'Fecha',
            'Comunidad',
            'Bombero',
            'Total Recaudado',
            'Comisión',
            'Neto',
        ];
    }

    public function map($capture): array
    {
        return [
            $capture->year,
            $capture->requirement_number ?? '',
            $capture->date ? \Carbon\Carbon::parse($capture->date)->format('d/m/Y') : '',
            $capture->community->name ?? '',
            $capture->firefighter->name ?? '',
            number_format($capture->subtotal, 2),
            number_format($capture->commission, 2),
            number_format($capture->subtotal - $capture->commission, 2),
        ];
    }
}
