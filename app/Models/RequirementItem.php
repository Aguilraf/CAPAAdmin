<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequirementItem extends Model
{
    protected $fillable = [
        'requirement_id',
        'partida_id',
        'description',
        'amount',
        'cfe_rpu',
        'cfe_town',
        'cfe_address',
        'cfe_uuid',
        'cfe_period_start',
        'cfe_period_end',
        'cfe_subtotal',
        'cfe_iva',
        'cfe_rounding',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cfe_subtotal' => 'decimal:2',
        'cfe_iva' => 'decimal:2',
        'cfe_rounding' => 'decimal:2',
        'cfe_period_start' => 'date',
        'cfe_period_end' => 'date',
    ];

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function partida()
    {
        return $this->belongsTo(Partida::class);
    }
}
