<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use SoftDeletes, \Illuminate\Database\Eloquent\Factories\HasFactory, \App\Traits\HasOrganismo;

    protected $table = 'empleados';

    protected $fillable = [
        'clave',
        'nombre',
        'puesto',
        'cargo',
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
        'fotografia',
        'fecha_baja',
        'activo',
        'es_gerente',
        'es_sindicalizado',
        'fecha_nacimiento',
        'sexo',
        'jefe_inmediato',
        'clabe',
        'banco',
        'tipo_plaza',
        'area_adscripcion',
        'primer_nombre',
        'primer_apellido',
        'segundo_apellido',
        'primer_nombre',
        'primer_apellido',
        'segundo_apellido',
        'puesto_id',
        'organismo_id',
    ];

    protected $casts = [
        'fecha_alta' => 'date',
        'fecha_baja' => 'date',
        'salario_diario' => 'decimal:2',
        'salario_mensual' => 'decimal:2',
        'activo' => 'boolean',
        'es_gerente' => 'boolean',
        'es_sindicalizado' => 'boolean',
        'fecha_nacimiento' => 'date',
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

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function periodosVacacionales()
    {
        return $this->hasMany(PeriodoVacacional::class);
    }

    public function bonosEvaluacion()
    {
        return $this->hasMany(BonoEvaluacion::class);
    }

    public function solicitudesVacaciones()
    {
        return $this->hasMany(SolicitudVacaciones::class);
    }

    public function puesto()
    {
        return $this->belongsTo(Puesto::class);
    }
}
