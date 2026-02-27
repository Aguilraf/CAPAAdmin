<?php

namespace App\Imports;

use App\Models\Partida;
use App\Models\Capitulo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PartidasImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['codigo'])) {
            return null;
        }

        $capituloId = null;
        if (!empty($row['capitulo'])) {
            $capitulo = Capitulo::where('codigo', trim($row['capitulo']))->first();
            $capituloId = $capitulo ? $capitulo->id : null;
        }

        return Partida::updateOrCreate(
            ['codigo' => trim($row['codigo'])],
            [
                'capitulo_id' => $capituloId,
                'subcapitulo' => $row['subcapitulo'] ?? null,
                'partida_generica' => $row['partida_generica'] ?? null,
                'nombre' => $row['nombre'] ?? '',
                'descripcion' => $row['descripcion'] ?? null,
                'activo' => (strtoupper($row['activo'] ?? '') === 'NO' ? false : true),
            ]
        );
    }
}
