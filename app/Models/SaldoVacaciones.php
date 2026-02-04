<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaldoVacaciones extends Model
{
    use HasFactory;

    protected $table = 'saldos_vacaciones';

    protected $fillable = [
        'periodo_vacacional_id',
        'tipo', // ORDINARIO, ANTIGUEDAD, SUTECAPA
        'total_dias',
        'dias_usados',
        'dias_pendientes',
    ];

    public function periodo()
    {
        return $this->belongsTo(PeriodoVacacional::class, 'periodo_vacacional_id');
    }

    public function getDiasDisponiblesAttribute()
    {
        return $this->total_dias - $this->dias_usados - $this->dias_pendientes;
    }
}
