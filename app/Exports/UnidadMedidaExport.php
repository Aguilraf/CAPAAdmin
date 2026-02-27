<?php

namespace App\Exports;

use App\Models\UnidadMedida;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;

class UnidadMedidaExport implements FromCollection, WithHeadings, WithTitle, WithMapping
{
    public function collection()
    {
        return UnidadMedida::all();
    }

    public function headings(): array
    {
        return ['NOMBRE'];
    }

    public function title(): string
    {
        return 'Unidades_Medida';
    }

    public function map($unidad): array
    {
        return [
            $unidad->nombre
        ];
    }
}
