<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleSolicitud extends Model
{
    use HasFactory;

    protected $table = 'detalles_solicitud';

    protected $fillable = [
        'solicitud_id',
        'origen_tipo',
        'origen_id',
        'dias_tomados',
        'numero_oficio',
    ];

    public function solicitud()
    {
        return $this->belongsTo(SolicitudVacaciones::class, 'solicitud_id');
    }

    public function origen()
    {
        return $this->morphTo(null, 'origen_tipo', 'origen_id');
    }
}
