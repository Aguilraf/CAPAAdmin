<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmpleadosTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['EMPLEADO001', 'JUAN PEREZ LOPEZ', 'JEFE DE OPERACION', 'DEPTO DE OPERACION', '22/11/2024', '123456789', 'JUAP850101XXX', 'NIVEL 1', 'JUPL850101HDFXXX01', 'BASE'],
        ];
    }

    public function headings(): array
    {
        return ['CLAVE', 'NOMBRE', 'PUESTO', 'DEPARTAMENTO', 'F. ALTA', 'NSS', 'RFC', 'NIVEL', 'CURP', 'OTRO'];
    }
}
