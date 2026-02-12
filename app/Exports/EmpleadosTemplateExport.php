<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmpleadosTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'EMPLEADO001',          // CLAVE
                'JUAN PEREZ LOPEZ',     // NOMBRE
                'JEFE DE OPERACION',    // PUESTO
                'ENCARGADO DE AREA',    // CARGO
                'DEPTO DE OPERACION',   // DEPARTAMENTO
                'OPERATIVA',            // AREA_ADSCRIPCION
                'BASE',                 // TIPO_PLAZA
                '22/11/2024',           // F. ALTA
                '12345678901',          // NSS
                'JUAP850101XXX',        // RFC
                'JUPL850101HDFXXX01',   // CURP
                'BASE',                 // CATEGORIA
                'NIVEL 1',              // NIVEL
                'BANCOMER',             // BANCO
                '012345678901234567',   // CLABE_INTERBANCARIA
                'juan@ejemplo.com',     // EMAIL
                '9991234567',           // TELEFONO
                'NO',                   // ES_GERENTE
                'JEFE001',              // JEFE_INMEDIATO (Clave)
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'CLAVE',
            'NOMBRE',
            'PUESTO',
            'CARGO',
            'DEPARTAMENTO',
            'AREA_ADSCRIPCION',
            'TIPO_PLAZA',
            'F. ALTA',
            'NSS',
            'RFC',
            'CURP',
            'CATEGORIA',
            'NIVEL',
            'BANCO',
            'CLABE_INTERBANCARIA',
            'EMAIL',
            'TELEFONO',
            'ES_GERENTE',
            'JEFE_INMEDIATO'
        ];
    }
}
