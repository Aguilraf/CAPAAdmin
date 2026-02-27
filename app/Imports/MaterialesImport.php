<?php

namespace App\Imports;

use App\Models\Material;
use App\Models\UnidadMedida;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MaterialesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['articulo'])) {
            return null;
        }

        $unidadId = null;
        if (!empty($row['unidad_medida'])) {
            $unidad = UnidadMedida::where('nombre', trim($row['unidad_medida']))->first();
            if (!$unidad) {
                $unidad = UnidadMedida::create(['nombre' => trim($row['unidad_medida'])]);
            }
            $unidadId = $unidad->id;
        }

        return Material::updateOrCreate(
            ['articulo' => trim($row['articulo'])],
            [
                'cantidad' => $row['cantidad'] ?? 0,
                'unidad_medida_id' => $unidadId,
                'es_default' => (strtoupper($row['es_default'] ?? '') === 'SI'),
            ]
        );
    }
}
