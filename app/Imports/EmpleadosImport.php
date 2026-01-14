<?php

namespace App\Imports;

use App\Models\Empleado;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class EmpleadosImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Skip if clave is empty
        if (empty($row['clave'])) {
            return null;
        }

        // Parse fecha_alta
        $fechaAlta = null;
        if (!empty($row['f_alta'])) {
            try {
                // Handle various date formats
                if (is_numeric($row['f_alta'])) {
                    // Excel date serial number
                    $fechaAlta = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['f_alta']);
                } else {
                    $fechaAlta = Carbon::parse($row['f_alta']);
                }
            } catch (\Exception $e) {
                $fechaAlta = now();
            }
        } else {
            $fechaAlta = now();
        }

        // Determine categoria based on "otro" column or default to BASE
        $categoria = 'BASE';
        if (!empty($row['otro'])) {
            $otro = strtoupper(trim($row['otro']));
            if (str_contains($otro, 'CONFIANZA')) {
                $categoria = 'CONFIANZA';
            }
        }

        return new Empleado([
            'clave' => $row['clave'] ?? '',
            'nombre' => strtoupper($row['nombre'] ?? ''),
            'puesto' => strtoupper($row['puesto'] ?? ''),
            'departamento' => strtoupper($row['departamento'] ?? ''),
            'rfc' => strtoupper($row['rfc'] ?? ''),
            'categoria' => $categoria,
            'fecha_alta' => $fechaAlta,
            'nivel' => $row['nivel'] ?? null,
            'curp' => strtoupper($row['curp'] ?? ''),
            'nss' => $row['nss'] ?? null,
            'afiliacion' => $row['otro'] ?? null,
            'activo' => true,
        ]);
    }
}
