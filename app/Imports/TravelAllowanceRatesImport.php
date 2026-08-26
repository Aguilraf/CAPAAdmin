<?php

namespace App\Imports;

use App\Models\TravelAllowanceRate;
use App\Models\Partida;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TravelAllowanceRatesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $partidaCode = trim($row['partida'] ?? '');
        if (empty($partidaCode)) {
            return null;
        }

        $partida = Partida::where('codigo', $partidaCode)->first();
        if (!$partida) {
            return null;
        }

        // Encodings from 'AÑO' might slugify to 'ano', 'ao', 'a_o', etc.
        $year = $row['ano'] ?? $row['ao'] ?? $row['a_o'] ?? date('Y');

        return TravelAllowanceRate::updateOrCreate(
            [
                'partida_id' => $partida->id,
                'cargo' => trim($row['cargo'] ?? ''),
                'nivel' => trim($row['nivel'] ?? ''),
                'year' => $year,
                'rate_type' => trim($row['tipo_tarifa'] ?? 'viaticos')
            ],
            [
                'zona_1_amount' => $row['monto_zona_1'] ?? 0,
                'zona_2_amount' => $row['monto_zona_2'] ?? 0,
                'budget_code' => $row['codigo_presupuestal'] ?? null,
                'active' => (strtoupper($row['activo'] ?? '') === 'NO' ? false : true),
            ]
        );
    }
}
