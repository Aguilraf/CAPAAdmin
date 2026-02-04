<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoVacacional extends Model
{
    use HasFactory;

    protected $table = 'periodos_vacacionales';

    protected $fillable = [
        'empleado_id',
        'anio',
        'numero_periodo',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function saldos()
    {
        return $this->hasMany(SaldoVacaciones::class);
    }
}
