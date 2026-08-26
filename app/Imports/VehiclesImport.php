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
        // Headers from export: INVENTARIO, UNIDAD, MARCA, TIPO, COLOR, MODELO, SERIE, MOTOR, PLACA, AREA, UBICACION, RESGUARDANTE, ORGANISMO

        $inventoryNumber = trim($row['inventario'] ?? $row['numero_inventario'] ?? '');
        $serialNumber = trim($row['serie'] ?? $row['numero_serie'] ?? '');

        if (empty($inventoryNumber) && empty($serialNumber)) {
            return null;
        }

        // Find Organismo by name (if provided)
        $organismoId = null;
        if (!empty($row['organismo'])) {
            $organismo = Organismo::where('nombre', 'like', '%' . trim($row['organismo']) . '%')->first();
            $organismoId = $organismo ? $organismo->id : null;
        }

        return Vehicle::updateOrCreate(
            ['inventory_number' => $inventoryNumber ?: $serialNumber],
            [
                'organismo_id' => $organismoId,
                'unit_type' => $row['unidad'] ?? $row['tipo_unidad'] ?? null,
                'brand' => $row['marca'] ?? null,
                'vehicle_type' => $row['tipo'] ?? $row['tipo_vehiculo'] ?? null,
                'color' => $row['color'] ?? null,
                'model_year' => $row['modelo'] ?? null,
                'serial_number' => $serialNumber,
                'engine_number' => $row['motor'] ?? null,
                'plate_number' => $row['placa'] ?? $row['placas'] ?? null,
                'area' => $row['area'] ?? null,
                'location' => $row['ubicacion'] ?? null,
                'custodian' => $row['resguardante'] ?? null,
                'active' => (strtoupper($row['activo'] ?? '') === 'NO' ? false : true),
            ]
        );
    }
}
