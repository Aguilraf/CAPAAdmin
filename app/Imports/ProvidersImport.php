<?php

namespace App\Imports;

use App\Models\Provider;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProvidersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['rfc']) && empty($row['nombre'])) {
            return null;
        }

        return Provider::updateOrCreate(
            ['rfc' => trim($row['rfc'] ?? '')],
            [
                'name' => $row['nombre'] ?? '',
                'bank_name' => $row['banco'] ?? null,
                'account_number' => $row['numero_cuenta'] ?? null,
                'clabe' => $row['clabe'] ?? null,
                'active' => (strtoupper($row['activo'] ?? '') === 'NO' ? false : true),
            ]
        );
    }
}
