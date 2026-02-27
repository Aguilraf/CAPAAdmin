<?php

namespace App\Exports;

use App\Models\Firefighter;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;

class FirefightersExport implements FromCollection, WithHeadings, WithTitle, WithMapping
{
    public function collection()
    {
        return Firefighter::with('community')->get();
    }

    public function headings(): array
    {
        return ['NOMBRE', 'COMUNIDAD', 'TELEFONO', 'GEOLOCALIZACION', 'ACTIVO'];
    }

    public function title(): string
    {
        return 'Bomberos';
    }

    public function map($firefighter): array
    {
        return [
            $firefighter->name,
            $firefighter->community ? $firefighter->community->name : '',
            $firefighter->contact_number,
            $firefighter->geolocation,
            $firefighter->active ? 'SI' : 'NO',
        ];
    }
}
