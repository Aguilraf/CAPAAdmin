<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BonoEvaluacion extends Model
{
    use HasFactory;

    protected $table = 'bonos_evaluacion';

    protected $fillable = [
        'empleado_id',
        'anio',
        'cuatrimestre',
        'calificacion', // Storing the "dias pagados" here or separate? Table has 'calificacion' decimal and 'dias_otorgados' int.
        // User prompt says: "dias le pagaron" (0, 5, 10, 15). We can map this to 'calificacion' or add a field.
        // The table definition has: decimal('calificacion', 5, 2) AND integer('dias_otorgados').
        // We can treat 'dias_pagados' (0,5,10,15) as the 'calificacion' (it's a score effectively) and 'dias_otorgados' (0,1,2,3) as the result.
        'dias_otorgados',
        'dias_usados',
        'fecha_expiracion',
    ];

    protected $casts = [
        'fecha_expiracion' => 'date',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
