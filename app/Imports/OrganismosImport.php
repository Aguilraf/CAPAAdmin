<?php

namespace App\Imports;

use App\Models\Organismo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OrganismosImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['nombre'])) {
            return null;
        }

        return Organismo::updateOrCreate(
            ['nombre' => trim($row['nombre'])],
            [
                'direccion' => $row['direccion'] ?? null,
                'telefono' => $row['telefono'] ?? null,
                'correo' => $row['correo'] ?? null,
                'ubicacion' => $row['ubicacion'] ?? null,
            ]
        );
    }
}
