<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leyenda extends Model
{
    use SoftDeletes;

    protected $table = 'leyendas';

    protected $fillable = [
        'user_id',
        'anio',
        'texto',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    // Relación con el usuario creador
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope para obtener la leyenda activa
    public function scopeActiva($query)
    {
        return $query->where('activa', true)->first();
    }
}
