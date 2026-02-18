<?php

namespace App\Imports;

use App\Models\Vehicle;
use App\Models\Organismo;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VehicleImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Skip empty rows
            if (!isset($row['inventario']) && !isset($row['serie'])) {
                continue;
            }

            // Find Organismo by name (if provided)
            $organismoId = null;
            if (isset($row['organismo'])) {
                $organismo = Organismo::where('nombre', 'like', '%' . trim($row['organismo']) . '%')->first();
                $organismoId = $organismo ? $organismo->id : null;
            }

            // Search for existing vehicle by Inventory Number OR Serial Number
            $vehicle = null;
            if (isset($row['inventario'])) {
                $vehicle = Vehicle::where('inventory_number', trim($row['inventario']))->first();
            }
            if (!$vehicle && isset($row['serie'])) {
                $vehicle = Vehicle::where('serial_number', trim($row['serie']))->first();
            }

            $data = [
                'organismo_id' => $organismoId,
                'inventory_number' => isset($row['inventario']) ? trim($row['inventario']) : null,
                'unit_type' => isset($row['unidad']) ? trim($row['unidad']) : null,
                'brand' => isset($row['marca']) ? trim($row['marca']) : null,
                'vehicle_type' => isset($row['tipo']) ? trim($row['tipo']) : null,
                'color' => isset($row['color']) ? trim($row['color']) : null,
                'model_year' => isset($row['modelo']) ? trim($row['modelo']) : null,
                'serial_number' => isset($row['serie']) ? trim($row['serie']) : null,
                'engine_number' => isset($row['motor']) ? trim($row['motor']) : null,
                'plate_number' => isset($row['placa']) ? trim($row['placa']) : null,
                'area' => isset($row['area']) ? trim($row['area']) : null,
                'location' => isset($row['ubicacion']) ? trim($row['ubicacion']) : null,
                'custodian' => isset($row['resguardante']) ? trim($row['resguardante']) : null,
                'active' => true,
            ];

            // Remove null values to avoid overwriting with null if updating
            $data = array_filter($data, function ($value) {
                return $value !== null;
            });

            if ($vehicle) {
                $vehicle->update($data);
            } else {
                // For create, ensure strict requirements or handle gracefully
                // Minimal required fields: inventory, brand, serial
                if (isset($data['inventory_number'])) {
                    Vehicle::create($data);
                }
            }
        }
    }
}
