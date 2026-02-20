<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organismo_id',
        'payment_date',
        'beneficiary_type',
        'beneficiary_id',
        'beneficiary',
        'amount',
        'amount_letters',
        'requirement_id',
        'concept',
        'payment_type',
        'reference',
        'elaborated_by_id',
        'formulated_by_id',
        'authorized_by_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function organismo()
    {
        return $this->belongsTo(Organismo::class);
    }

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function elaboratedBy()
    {
        return $this->belongsTo(Empleado::class, 'elaborated_by_id');
    }

    public function formulatedBy()
    {
        return $this->belongsTo(Empleado::class, 'formulated_by_id');
    }

    public function authorizedBy()
    {
        return $this->belongsTo(Empleado::class, 'authorized_by_id');
    }
}
