<?php

namespace App\Imports;

use App\Models\Firefighter;
use App\Models\Community;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FirefightersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['nombre'])) {
            return null;
        }

        $communityId = null;
        if (!empty($row['comunidad'])) {
            $community = Community::where('name', trim($row['comunidad']))->first();
            if (!$community) {
                $community = Community::create(['name' => trim($row['comunidad'])]);
            }
            $communityId = $community->id;
        }

        return Firefighter::updateOrCreate(
            ['name' => trim($row['nombre'])],
            [
                'community_id' => $communityId,
                'contact_number' => $row['telefono'] ?? null,
                'geolocation' => $row['geolocalizacion'] ?? null,
                'active' => (strtoupper($row['activo'] ?? '') === 'NO' ? false : true),
            ]
        );
    }
}
