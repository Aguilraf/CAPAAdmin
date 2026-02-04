<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entitlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'empleado_id',
        'year',
        'type', // ORDINARIO, ANTIGUEDAD, SUTECAPA, BONO_CUATRIMESTRAL
        'description',
        'total_days',
        'used_days',
        'pending_days',
        'valid_from',
        'valid_until',
        'status', // ACTIVE, EXPIRED, EXHAUSTED
        'meta',   // JSON metadata
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'meta' => 'array',
    ];

    // Relationships
    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
