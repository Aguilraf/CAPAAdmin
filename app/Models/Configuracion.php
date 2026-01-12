<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuracion';

    protected $fillable = [
        'nombre_empresa',
        'nombre_organismo',
        'logo',
        'iva',
    ];

    protected $casts = [
        'iva' => 'decimal:2',
    ];

    // Método estático para obtener la configuración
    public static function obtener()
    {
        return self::first();
    }
}
