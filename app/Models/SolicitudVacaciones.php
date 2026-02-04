<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudVacaciones extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_vacaciones';

    protected $fillable = [
        'empleado_id',
        'tipo_solicitud', // VACACION, ONOMASTICO, DEFUNCION, NACIMIENTO
        'fecha_inicio',
        'fecha_fin',
        'dias_solicitados',
        'motivo',
        'estado', // PENDIENTE, APROBADA, RECHAZADA, CANCELADA
        'aprobado_por',
        'fecha_aprobacion',
        'comentarios_rechazo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_aprobacion' => 'datetime',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function entitlements()
    {
        return $this->belongsToMany(Entitlement::class, 'request_entitlements', 'solicitud_id', 'entitlement_id')
            ->withPivot('days_taken', 'numero_oficio')
            ->withTimestamps();
    }
}
