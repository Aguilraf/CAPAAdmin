<?php

namespace App\Exports;

use App\Models\Puesto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;

class PuestosExport implements FromCollection, WithHeadings, WithTitle, WithMapping
{
    public function collection()
    {
        return Puesto::all();
    }

    public function headings(): array
    {
        return ['NOMBRE', 'NIVEL', 'DESCRIPCION', 'ACTIVO'];
    }

    public function title(): string
    {
        return 'Puestos';
    }

    public function map($puesto): array
    {
        return [
            $puesto->nombre,
            $puesto->nivel,
            $puesto->descripcion,
            $puesto->activo ? 'SI' : 'NO'
        ];
    }
}
