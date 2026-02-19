<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelAllowance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'requirement_id',
        'oficio_number',
        'commission_summary_legend',
        'exercise_year',
        'quarter',
        'commissioner_id',
        'origin_country',
        'origin_state',
        'origin_city',
        'destination_country',
        'destination_state',
        'destination_city',
        'departure_date',
        'return_date',
        'days_duration',
        'half_day_payment',
        'justification',
        'has_viaticos',
        'viaticos_partida_id',
        'has_pasaje',
        'pasaje_partida_id',
        'has_hospedaje',
        'hospedaje_partida_id',
        'transport_type',
        'vehicle_id',
        'invoice_folio',
        'invoice_date',
        'provider_rfc',
        'provider_name',
        'uuid',
        'report_date',
        'report_link',
        'subtotal',
        'iva',
        'isr',
        'retention_iva',
        'total',
    ];

    protected $casts = [
        'departure_date' => 'datetime',
        'return_date' => 'datetime',
        'invoice_date' => 'date',
        'has_viaticos' => 'boolean',
        'has_pasaje' => 'boolean',
        'has_hospedaje' => 'boolean',
        'half_day_payment' => 'boolean',
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'isr' => 'decimal:2',
        'total' => 'decimal:2',
        'report_date' => 'date',
    ];

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function commissioner()
    {
        return $this->belongsTo(Empleado::class, 'commissioner_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function commissioners()
    {
        return $this->belongsToMany(Empleado::class, 'travel_allowance_commissioners', 'travel_allowance_id', 'employee_id')
            ->withPivot(['oficio_number', 'report_date', 'report_link'])
            ->withTimestamps();
    }

    public function viaticosPartida()
    {
        return $this->belongsTo(Partida::class, 'viaticos_partida_id');
    }

    public function pasajePartida()
    {
        return $this->belongsTo(Partida::class, 'pasaje_partida_id');
    }

    public function hospedajePartida()
    {
        return $this->belongsTo(Partida::class, 'hospedaje_partida_id');
    }
}
