<?php

namespace App\Exports;

use App\Models\Material;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaterialesExport implements FromCollection, WithHeadings, WithTitle, WithMapping
{
    public function collection()
    {
        return Material::with('unidadMedida')->get();
    }

    public function headings(): array
    {
        return ['ARTICULO', 'CANTIDAD', 'UNIDAD MEDIDA', 'ES DEFAULT'];
    }

    public function title(): string
    {
        return 'Materiales';
    }

    public function map($material): array
    {
        return [
            $material->articulo,
            $material->cantidad,
            $material->unidadMedida ? $material->unidadMedida->nombre : '',
            $material->es_default ? 'SI' : 'NO'
        ];
    }
}
