<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use SoftDeletes, \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'empleados';

    protected $fillable = [
        'clave',
        'nombre',
        'puesto',
        'departamento',
        'rfc',
        'categoria',
        'fecha_alta',
        'nivel',
        'salario_diario',
        'salario_mensual',
        'curp',
        'nss',
        'afiliacion',
        'email',
        'telefono',
        'numero_empleado',
        'fotografia',
        'fecha_baja',
        'activo',
        'es_gerente',
    ];

    protected $casts = [
        'fecha_alta' => 'date',
        'fecha_baja' => 'date',
        'salario_diario' => 'decimal:2',
        'salario_mensual' => 'decimal:2',
        'activo' => 'boolean',
        'es_gerente' => 'boolean',
    ];

    protected $appends = ['antiguedad'];

    /**
     * Calcula la antigüedad en años, meses y días.
     */
    public function getAntiguedadAttribute()
    {
        if (!$this->fecha_alta) {
            return 'Sin fecha de alta';
        }

        $fechaFin = $this->fecha_baja ?: now();
        $diff = $this->fecha_alta->diff($fechaFin);

        $parts = [];
        if ($diff->y > 0)
            $parts[] = "{$diff->y} años";
        if ($diff->m > 0)
            $parts[] = "{$diff->m} meses";
        if ($diff->d > 0)
            $parts[] = "{$diff->d} días";

        return empty($parts) ? '0 días' : implode(' ', $parts);
    }

    /**
     * Boot method to ensure only one active manager
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($empleado) {
            // If this employee is being set as gerente and is active
            if ($empleado->es_gerente && $empleado->activo) {
                // Remove es_gerente from all other active employees
                static::where('activo', true)
                    ->where('es_gerente', true)
                    ->where('id', '!=', $empleado->id)
                    ->update(['es_gerente' => false]);
            }
        });
    }

    // Scope para empleados activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
