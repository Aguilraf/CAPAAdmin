<?php

namespace App\Exports;

use App\Models\TravelAllowanceRate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;

class TravelAllowanceRatesExport implements FromCollection, WithHeadings, WithTitle, WithMapping
{
    public function collection()
    {
        return TravelAllowanceRate::with('partida')->get();
    }

    public function headings(): array
    {
        return [
            'PARTIDA',
            'CARGO',
            'NIVEL',
            'MONTO ZONA 1',
            'MONTO ZONA 2',
            'TIPO TARIFA',
            'CODIGO PRESUPUESTAL',
            'AÑO',
            'ACTIVO'
        ];
    }

    public function title(): string
    {
        return 'Tarifas_Viaticos';
    }

    public function map($rate): array
    {
        return [
            $rate->partida ? $rate->partida->codigo : '',
            $rate->cargo,
            $rate->nivel,
            $rate->zona_1_amount,
            $rate->zona_2_amount,
            $rate->rate_type,
            $rate->budget_code,
            $rate->year,
            $rate->active ? 'SI' : 'NO'
        ];
    }
}
