<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Capitulo extends Model
{
    use SoftDeletes;

    protected $table = 'capitulos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relación con partidas
    public function partidas()
    {
        return $this->hasMany(Partida::class);
    }

    // Scope para capítulos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
