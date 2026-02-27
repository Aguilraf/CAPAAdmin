<?php

namespace App\Exports;

use App\Models\Partida;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;

class PartidasExport implements FromCollection, WithHeadings, WithTitle, WithMapping
{
    public function collection()
    {
        return Partida::with('capitulo')->get();
    }

    public function headings(): array
    {
        return ['CAPITULO', 'SUBCAPITULO', 'PARTIDA GENERICA', 'CODIGO', 'NOMBRE', 'DESCRIPCION', 'ACTIVO'];
    }

    public function title(): string
    {
        return 'Partidas';
    }

    public function map($partida): array
    {
        return [
            $partida->capitulo ? $partida->capitulo->codigo : '',
            $partida->subcapitulo,
            $partida->partida_generica,
            $partida->codigo,
            $partida->nombre,
            $partida->descripcion,
            $partida->activo ? 'SI' : 'NO'
        ];
    }
}
