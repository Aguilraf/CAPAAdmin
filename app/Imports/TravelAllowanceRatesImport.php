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
        if (empty($row['partida']) || empty($row['cargo']) || empty($row['nivel'])) {
            return null;
        }

        $partida = Partida::where('codigo', trim($row['partida']))->first();
        if (!$partida)
            return null;

        return TravelAllowanceRate::updateOrCreate(
            [
                'partida_id' => $partida->id,
                'cargo' => trim($row['cargo']),
                'nivel' => trim($row['nivel']),
                'year' => $row['ano'] ?? date('Y'),
                'rate_type' => $row['tipo_tarifa'] ?? 'viaticos'
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
