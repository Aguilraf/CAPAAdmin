<?php

namespace App\Exports;

use App\Models\Capitulo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;

class CapitulosExport implements FromCollection, WithHeadings, WithTitle, WithMapping
{
    public function collection()
    {
        return Capitulo::all();
    }

    public function headings(): array
    {
        return ['CODIGO', 'NOMBRE', 'DESCRIPCION', 'ACTIVO'];
    }

    public function title(): string
    {
        return 'Capitulos';
    }

    public function map($capitulo): array
    {
        return [
            $capitulo->codigo,
            $capitulo->nombre,
            $capitulo->descripcion,
            $capitulo->activo ? 'SI' : 'NO'
        ];
    }
}
