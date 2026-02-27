<?php

namespace App\Exports;

use App\Models\Organismo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrganismosExport implements FromCollection, WithHeadings, WithTitle, WithMapping
{
    public function collection()
    {
        return Organismo::all();
    }

    public function headings(): array
    {
        return ['NOMBRE', 'DIRECCION', 'TELEFONO', 'CORREO', 'UBICACION'];
    }

    public function title(): string
    {
        return 'Organismos';
    }

    public function map($organismo): array
    {
        return [
            $organismo->nombre,
            $organismo->direccion,
            $organismo->telefono,
            $organismo->correo,
            $organismo->ubicacion
        ];
    }
}
