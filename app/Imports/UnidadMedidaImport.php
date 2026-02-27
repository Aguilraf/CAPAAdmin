<?php

namespace App\Imports;

use App\Models\UnidadMedida;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UnidadMedidaImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['nombre'])) {
            return null;
        }

        return UnidadMedida::firstOrCreate(
            ['nombre' => trim($row['nombre'])]
        );
    }
}
