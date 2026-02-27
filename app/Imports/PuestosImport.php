<?php

namespace App\Imports;

use App\Models\Puesto;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PuestosImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['nombre'])) {
            return null;
        }

        return Puesto::updateOrCreate(
            ['nombre' => trim($row['nombre'])],
            [
                'nivel' => $row['nivel'] ?? null,
                'descripcion' => $row['descripcion'] ?? null,
                'activo' => (strtoupper($row['activo'] ?? '') === 'NO' ? false : true),
            ]
        );
    }
}
