<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = [
        'articulo',
        'cantidad',
        'es_default',
        'unidad_medida_id',
    ];

    protected $casts = [
        'es_default' => 'boolean',
    ];

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    //
}
