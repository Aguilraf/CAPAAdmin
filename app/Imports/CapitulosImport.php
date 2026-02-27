<?php

namespace App\Imports;

use App\Models\Capitulo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CapitulosImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['codigo'])) {
            return null;
        }

        return Capitulo::updateOrCreate(
            ['codigo' => trim($row['codigo'])],
            [
                'nombre' => $row['nombre'] ?? '',
                'descripcion' => $row['descripcion'] ?? null,
                'activo' => (strtoupper($row['activo'] ?? '') === 'NO' ? false : true),
            ]
        );
    }
}
