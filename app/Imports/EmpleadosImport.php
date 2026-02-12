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
                    $fechaAlta = Carbon::parse($row['f_alta']);
                }
            } catch (\Exception $e) {
                $fechaAlta = now();
            }
        } else {
            $fechaAlta = now();
        }

        // Categoria logic (simplified based on new template)
        $categoria = strtoupper($row['categoria'] ?? 'BASE');
        if (!in_array($categoria, ['BASE', 'CONFIANZA'])) {
            // Fallback logic could go here, for now default to BASE if invalid
            $categoria = 'BASE';
        }

        // Determine es_gerente
        $esGerente = false;
        if (!empty($row['es_gerente'])) {
            $val = strtoupper(trim($row['es_gerente']));
            $esGerente = in_array($val, ['SI', 'YES', 'TRUE', '1']);
        }

        // Jefe inmediato logic (optional, try to find ID by clave)
        // Note: Import might be tricky if jefe is not yet imported. 
        // For simplicity, we store the TEXT value if column is string, 
        // BUT the DB column 'jefe_inmediato' is nullable|string|max:255 based on validation,
        // so we can store the Clave or Name directly.
        $jefeInmediato = $row['jefe_inmediato'] ?? null;

        return new Empleado([
            'clave' => $row['clave'],
            'nombre' => strtoupper($row['nombre'] ?? ''),
            'puesto' => strtoupper($row['puesto'] ?? ''),
            'cargo' => strtoupper($row['cargo'] ?? ''),
            'departamento' => strtoupper($row['departamento'] ?? ''),
            'area_adscripcion' => strtoupper($row['area_adscripcion'] ?? ''),
            'tipo_plaza' => strtoupper($row['tipo_plaza'] ?? ''),
            'rfc' => strtoupper($row['rfc'] ?? ''),
            'categoria' => $categoria,
            'es_sindicalizado' => ($categoria === 'BASE'),
            'fecha_alta' => $fechaAlta,
            'nivel' => strtoupper($row['nivel'] ?? ''),
            'banco' => strtoupper($row['banco'] ?? ''),
            'clabe' => $row['clabe_interbancaria'] ?? null,
            'curp' => strtoupper($row['curp'] ?? ''),
            'nss' => $row['nss'] ?? null,
            'email' => $row['email'] ?? null,
            'telefono' => $row['telefono'] ?? null,
            'es_gerente' => $esGerente,
            'jefe_inmediato' => $jefeInmediato,
            'activo' => true,
        ]);
    }
}
