<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partida extends Model
{
    use SoftDeletes;

    protected $table = 'partidas';

    protected $fillable = [
        'capitulo_id',
        'subcapitulo',
        'partida_generica',
        'codigo',
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relación con capítulo
    public function capitulo()
    {
        return $this->belongsTo(Capitulo::class);
    }

    // Scope para partidas activas
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
