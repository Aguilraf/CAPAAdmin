<?php

namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;

class VehiclesExport implements FromCollection, WithHeadings, WithTitle, WithMapping
{
    public function collection()
    {
        return Vehicle::with('organismo')->get();
    }

    public function headings(): array
    {
        return [
            'INVENTARIO',
            'UNIDAD',
            'MARCA',
            'TIPO',
            'COLOR',
            'MODELO',
            'SERIE',
            'MOTOR',
            'PLACA',
            'AREA',
            'UBICACION',
            'RESGUARDANTE',
            'ORGANISMO'
        ];
    }

    public function title(): string
    {
        return 'Vehiculos';
    }

    public function map($vehicle): array
    {
        return [
            $vehicle->inventory_number,
            $vehicle->unit_type,
            $vehicle->brand,
            $vehicle->vehicle_type,
            $vehicle->color,
            $vehicle->model_year,
            $vehicle->serial_number,
            $vehicle->engine_number,
            $vehicle->plate_number,
            $vehicle->area,
            $vehicle->location,
            $vehicle->custodian,
            $vehicle->organismo ? $vehicle->organismo->nombre : ''
        ];
    }
}
