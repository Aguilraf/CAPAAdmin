<?php

namespace App\Exports;

use App\Models\Provider;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProvidersExport implements FromCollection, WithHeadings, WithTitle, WithMapping
{
    public function collection()
    {
        return Provider::all();
    }

    public function headings(): array
    {
        return ['NOMBRE', 'RFC', 'BANCO', 'NUMERO CUENTA', 'CLABE', 'ACTIVO'];
    }

    public function title(): string
    {
        return 'Proveedores';
    }

    public function map($provider): array
    {
        return [
            $provider->name,
            $provider->rfc,
            $provider->bank_name,
            $provider->account_number,
            $provider->clabe,
            $provider->active ? 'SI' : 'NO'
        ];
    }
}
