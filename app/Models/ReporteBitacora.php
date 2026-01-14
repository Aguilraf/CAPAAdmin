<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteBitacora extends Model
{
    protected $fillable = [
        'user_id',
        'empleado_id',
        'fecha_reporte',
        'destinatario_nombre',
        'destinatario_cargo',
        'solicitante_nombre',
        'solicitante_cargo',
        'solicitante_departamento',
        'materiales',
        'datos_completos',
    ];

    protected $casts = [
        'fecha_reporte' => 'date',
        'materiales' => 'array',
        'datos_completos' => 'array',
    ];

    /**
     * Get the user that created the report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee associated with the report.
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
