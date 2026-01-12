<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use SoftDeletes;

    protected $table = 'empleados';

    protected $fillable = [
        'clave',
        'nombre',
        'puesto',
        'departamento',
        'rfc',
        'categoria',
        'fecha_alta',
        'salario_diario',
        'salario_mensual',
        'curp',
        'email',
        'telefono',
        'numero_empleado',
        'fotografia',
        'fecha_baja',
        'activo',
    ];

    protected $casts = [
        'fecha_alta' => 'date',
        'fecha_baja' => 'date',
        'salario_diario' => 'decimal:2',
        'salario_mensual' => 'decimal:2',
        'activo' => 'boolean',
    ];

    // Scope para empleados activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
