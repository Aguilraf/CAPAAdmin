<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'uuid',
        'rfc_emisor',
        'nombre_emisor',
        'reg_emis',
        'rfc_receptor',
        'nombre_receptor',
        'reg_recep',
        'tipo',
        'numero_factura',
        'fecha',
        'subtotal',
        'ieps',
        'descuento',
        'base_16',
        'base_8',
        'base_0',
        'iva_16',
        'iva_8',
        'isr_ret',
        'iva_ret',
        'total',
        'uso',
        'forma_pago',
        'metodo_pago',
        'oi',
        'concepto',
        'uuid_relacionado',
        'tiporel',
        'url',
        'f_pago',
        'num_op',
        'cta_ordenante',
        'cta_beneficiario',
        'parc',
        's_anterior',
        'imp_pagado',
        'saldo_insoluto',
        'status',
        'is_used',
        'daily_income_id',
        'income_policy_id',
        'is_reconciled_without_income',
        'pending_status',
        'pending_note',
    ];

    const PENDING_STATUSES = [
        'cancelada' => 'Cancelada',
        'anio_anterior' => 'Año anterior',
        'otro' => 'Otro',
    ];

    public function dailyIncome()
    {
        return $this->belongsTo(DailyIncome::class);
    }

    public function incomePolicy()
    {
        return $this->belongsTo(IncomePolicy::class);
    }
}

