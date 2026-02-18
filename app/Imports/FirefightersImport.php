<?php

namespace App\Imports;

use App\Models\Firefighter;
use App\Models\Community;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class FirefightersImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public $imported = 0;
    public $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because index is 0-based and header is row 1

            // Debug header mapping issues if necessary
            // keys are slugged by default: 'Nombre' -> 'nombre', 'Comunidad ID' -> 'comunidad_id'

            $name = $row['nombre'] ?? null;
            $communityId = $row['comunidad_id'] ?? null;

            // If keys are missing, maybe the header row was not detected correctly or keys are different
            if (is_null($name) && is_null($communityId)) {
                $this->errors[] = "Fila {$rowNumber}: Columnas no encontradas. Verifique que el archivo tenga cabeceras 'nombre' y 'comunidad_id'.";
                continue;
            }

            if (empty($name) || empty($communityId)) {
                $this->errors[] = "Fila {$rowNumber}: Nombre o ID de comunidad vacío";
                continue;
            }

            // Clean data
            $name = trim($name);
            $communityId = preg_replace('/[^0-9]/', '', $communityId); // Extract numeric ID

            if (empty($communityId)) {
                $this->errors[] = "Fila {$rowNumber}: ID de comunidad no válido";
                continue;
            }

            // Validate community exists
            $community = Community::find($communityId);
            if (!$community) {
                $this->errors[] = "Fila {$rowNumber}: No existe comunidad con ID '{$communityId}'";
                continue;
            }

            try {
                Firefighter::create([
                    'name' => $name,
                    'community_id' => $communityId,
                    'active' => true,
                    'max_rounding_amount' => 0
                ]);
                $this->imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Fila {$rowNumber}: Error al guardar - " . $e->getMessage();
            }
        }
    }
}
