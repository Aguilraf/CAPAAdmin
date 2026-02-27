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
        'employee_id',
        'uuid',
        'invoice_folio',
        'invoice_date',
        'provider_rfc',
        'provider_name',
        'invoice_subtotal',
        'invoice_iva',
        'invoice_retention_isr',
        'invoice_retention_iva',
        'invoice_total',
        'cfe_rpu',
        'cfe_town',
        'cfe_address',
        'cfe_uuid',
        'cfe_period_start',
        'cfe_period_end',
        'cfe_subtotal',
        'cfe_iva',
        'cfe_rounding',
        'invoice_discount',
        'invoice_ieps',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'invoice_subtotal' => 'decimal:2',
        'invoice_iva' => 'decimal:2',
        'invoice_retention_isr' => 'decimal:2',
        'invoice_retention_iva' => 'decimal:2',
        'invoice_total' => 'decimal:2',
        'cfe_subtotal' => 'decimal:2',
        'cfe_iva' => 'decimal:2',
        'cfe_rounding' => 'decimal:2',
        'invoice_discount' => 'decimal:2',
        'invoice_ieps' => 'decimal:2',
        'invoice_date' => 'date',
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

    public function employee()
    {
        return $this->belongsTo(Empleado::class, 'employee_id');
    }
}
