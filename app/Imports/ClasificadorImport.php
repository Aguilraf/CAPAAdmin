<?php

namespace App\Imports;

use App\Models\Capitulo;
use App\Models\Partida;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;

class ClasificadorImport implements OnEachRow, WithStartRow
{
    private $currentCapitulo;
    private $currentSubcapitulo; // To store subchapter code context

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2; // Datos empiezan en fila 2 (fila 1 es encabezado)
    }

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        // 0: Capitulo
        // 1: Subcapítulo
        // 2: Partida. Genérica
        // 3: Partida. Específica
        // 4: Denominación

        // Use trim to handle white spaces or unexpected formatting
        $capituloCodigo = isset($row[0]) ? trim((string) $row[0]) : '';
        $subcapitulo = isset($row[1]) ? trim((string) $row[1]) : '';
        $generica = isset($row[2]) ? trim((string) $row[2]) : '';
        $especifica = isset($row[3]) ? trim((string) $row[3]) : '';
        $denominacion = isset($row[4]) ? trim((string) $row[4]) : '';

        // Skip completely empty lines
        if ($capituloCodigo === '' && $denominacion === '' && $especifica === '') {
            return;
        }

        // 1. Manejo de Capítulo
        if ($capituloCodigo !== '') {
            // Buscar si existe (incluso eliminado)
            $this->currentCapitulo = Capitulo::withTrashed()->where('codigo', $capituloCodigo)->first();

            if ($this->currentCapitulo) {
                // Si existe y está eliminado, restaurarlo
                if ($this->currentCapitulo->trashed()) {
                    $this->currentCapitulo->restore();
                }
                // Actualizar datos si es necesario (opcional, aquí solo aseguramos que tenga el nombre correcto si es definición pura)
                if ($denominacion !== '' && $subcapitulo === '' && $especifica === '') {
                    $this->currentCapitulo->update([
                        'nombre' => $denominacion,
                        'activo' => true
                    ]);
                }
            } else {
                // Crear nuevo si no existe
                $this->currentCapitulo = Capitulo::create([
                    'codigo' => $capituloCodigo,
                    'nombre' => $denominacion ?: 'Capítulo ' . $capituloCodigo,
                    'descripcion' => 'Importado desde Excel',
                    'activo' => true
                ]);
            }

            // Reset context when looking at a new chapter line
            $this->currentSubcapitulo = '';
        }

        // 2. Manejo de Subcapítulo (Contexto)
        // Si hay subcapítulo pero NO partida, es una definición de Subcapítulo
        if ($subcapitulo !== '' && $especifica === '') {
            $this->currentSubcapitulo = $subcapitulo;
            // No saved entity for subchapter, just context
        }

        // 3. Manejo de Partida
        // Requiere un capítulo contexto (actual) y código de partida específica
        if ($this->currentCapitulo && $especifica !== '') {
            // Buscar partida existente (incluso eliminada)
            $partida = Partida::withTrashed()->where('codigo', $especifica)->first();

            if ($partida) {
                // Restaurar si está eliminada
                if ($partida->trashed()) {
                    $partida->restore();
                }
                // Actualizar
                $partida->update([
                    'capitulo_id' => $this->currentCapitulo->id,
                    'subcapitulo' => $subcapitulo ?: $this->currentSubcapitulo,
                    'partida_generica' => $generica,
                    'nombre' => $denominacion ?: 'Sin nombre',
                    'descripcion' => 'Importado Masivamente',
                    'activo' => true
                ]);
            } else {
                // Crear nueva
                Partida::create([
                    'codigo' => $especifica,
                    'capitulo_id' => $this->currentCapitulo->id,
                    'subcapitulo' => $subcapitulo ?: $this->currentSubcapitulo, // Use row value or fallback to context
                    'partida_generica' => $generica,
                    'nombre' => $denominacion ?: 'Sin nombre',
                    'descripcion' => 'Importado Masivamente',
                    'activo' => true
                ]);
            }
        }
    }
}
