<?php

namespace App\Exports;

use App\Models\Empleado;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmpleadoExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Empleado::all();
    }

    public function headings(): array
    {
        return [
            'CLAVE',
            'NOMBRE',
            'PRIMER APELLIDO',
            'SEGUNDO APELLIDO',
            'PUESTO',
            'CARGO',
            'DEPARTAMENTO',
            'AREA ADSCRIPCIÓN',
            'TIPO PLAZA',
            'F. ALTA',
            'NSS',
            'RFC',
            'CURP',
            'CATEGORIA',
            'NIVEL',
            'BANCO',
            'CLABE INTERBANCARIA',
            'EMAIL',
            'TELEFONO',
            'ACTIVO',
            'ES GERENTE',
            'ANTIGUEDAD'
        ];
    }

    public function map($empleado): array
    {
        return [
            $empleado->clave,
            $empleado->nombre,
            $empleado->primer_apellido,
            $empleado->segundo_apellido,
            $empleado->puesto,
            $empleado->cargo,
            $empleado->departamento,
            $empleado->area_adscripcion,
            $empleado->tipo_plaza,
            $empleado->fecha_alta ? $empleado->fecha_alta->format('d/m/Y') : '',
            $empleado->nss,
            $empleado->rfc,
            $empleado->curp,
            $empleado->categoria,
            $empleado->nivel,
            $empleado->banco,
            $empleado->clabe,
            $empleado->email,
            $empleado->telefono,
            $empleado->activo ? 'SI' : 'NO',
            $empleado->es_gerente ? 'SI' : 'NO',
            $empleado->antiguedad,
        ];
    }
}
