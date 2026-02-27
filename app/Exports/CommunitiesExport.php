<?php

namespace App\Exports;

use App\Models\Community;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;

class CommunitiesExport implements FromCollection, WithHeadings, WithTitle, WithMapping
{
    public function collection()
    {
        return Community::all();
    }

    public function headings(): array
    {
        return ['NOMBRE', 'GEOLOCALIZACION', 'PORCENTAJE'];
    }

    public function title(): string
    {
        return 'Comunidades';
    }

    public function map($community): array
    {
        return [
            $community->name,
            $community->geolocation,
            $community->percentage,
        ];
    }
}
