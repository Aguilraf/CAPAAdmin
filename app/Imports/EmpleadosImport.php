<?php

namespace App\Imports;

use App\Models\Empleado;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class EmpleadosImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Skip if clave is empty
        if (empty($row['clave'])) {
            return null;
        }

        // Parse fecha_alta
        $fechaAlta = null;
        if (!empty($row['f_alta'])) {
            try {
                if (is_numeric($row['f_alta'])) {
                    $fechaAlta = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['f_alta']);
                } else {
                    $fechaAlta = Carbon::createFromFormat('d/m/Y', $row['f_alta']);
                }
            } catch (\Exception $e) {
                try {
                    $fechaAlta = Carbon::parse($row['f_alta']);
                } catch (\Exception $ex) {
                    $fechaAlta = now();
                }
            }
        }

        // Determine es_gerente and activo (SI/NO)
        $esGerente = false;
        if (!empty($row['es_gerente'])) {
            $val = strtoupper(trim($row['es_gerente']));
            $esGerente = in_array($val, ['SI', 'YES', 'TRUE', '1']);
        }

        $activo = true;
        if (isset($row['activo'])) {
            $val = strtoupper(trim($row['activo']));
            $activo = !in_array($val, ['NO', 'FALSE', '0']);
        }

        // Update or create based on CLAVE
        return Empleado::updateOrCreate(
            ['clave' => $row['clave']],
            [
                'nombre' => strtoupper($row['nombre'] ?? ''),
                'primer_apellido' => strtoupper($row['primer_apellido'] ?? ''),
                'segundo_apellido' => strtoupper($row['segundo_apellido'] ?? ''),
                'puesto' => strtoupper($row['puesto'] ?? ''),
                'cargo' => strtoupper($row['cargo'] ?? ''),
                'departamento' => strtoupper($row['departamento'] ?? ''),
                'area_adscripcion' => strtoupper($row['area_adscripcion'] ?? ''),
                'tipo_plaza' => strtoupper($row['tipo_plaza'] ?? ''),
                'rfc' => strtoupper($row['rfc'] ?? ''),
                'categoria' => strtoupper($row['categoria'] ?? 'BASE'),
                'es_sindicalizado' => (strtoupper($row['categoria'] ?? '') === 'BASE'),
                'fecha_alta' => $fechaAlta,
                'nivel' => strtoupper($row['nivel'] ?? ''),
                'banco' => strtoupper($row['banco'] ?? ''),
                'clabe' => $row['clabe_interbancaria'] ?? null,
                'curp' => strtoupper($row['curp'] ?? ''),
                'nss' => $row['nss'] ?? null,
                'email' => $row['email'] ?? null,
                'telefono' => $row['telefono'] ?? null,
                'es_gerente' => $esGerente,
                'activo' => $activo,
            ]
        );
    }
}
