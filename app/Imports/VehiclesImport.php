<?php

namespace App\Imports;

use App\Models\Vehicle;
use App\Models\Organismo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VehiclesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['numero_inventario']) && empty($row['numero_serie'])) {
            return null;
        }

        // Find Organismo by name (if provided)
        $organismoId = null;
        if (!empty($row['organismo'])) {
            $organismo = Organismo::where('nombre', 'like', '%' . trim($row['organismo']) . '%')->first();
            $organismoId = $organismo ? $organismo->id : null;
        }

        return Vehicle::updateOrCreate(
            ['inventory_number' => trim($row['numero_inventario'] ?? $row['numero_serie'])],
            [
                'organismo_id' => $organismoId,
                'unit_type' => $row['tipo_unidad'] ?? null,
                'brand' => $row['marca'] ?? null,
                'vehicle_type' => $row['tipo_vehiculo'] ?? null,
                'model_year' => $row['modelo'] ?? null,
                'serial_number' => $row['numero_serie'] ?? null,
                'plate_number' => $row['placas'] ?? null,
                'area' => $row['area'] ?? null,
                'location' => $row['ubicacion'] ?? null,
                'custodian' => $row['resguardante'] ?? null,
                'active' => (strtoupper($row['activo'] ?? '') === 'NO' ? false : true),
            ]
        );
    }
}
