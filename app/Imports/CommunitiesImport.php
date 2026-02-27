<?php

namespace App\Imports;

use App\Models\Community;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CommunitiesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['nombre'])) {
            return null;
        }

        return Community::updateOrCreate(
            ['name' => trim($row['nombre'])],
            [
                'geolocation' => $row['geolocalizacion'] ?? null,
                'percentage' => $row['porcentaje'] ?? 0,
            ]
        );
    }
}
