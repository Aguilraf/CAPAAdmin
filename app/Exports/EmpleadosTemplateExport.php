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
                'EMP001',               // CLAVE
                'JUAN',                 // NOMBRE
                'PEREZ',                // PRIMER APELLIDO
                'LOPEZ',                // SEGUNDO APELLIDO
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
                'SI',                   // ACTIVO
                'NO',                   // ES_GERENTE
                '0 años',               // ANTIGUEDAD
            ],
        ];
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
}
