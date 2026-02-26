<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    /** @use HasFactory<\Database\Factories\CommissionFactory> */
    use HasFactory;

    protected $fillable = [
        'empleado_id',
        'oficio_number',
        'start_date',
        'end_date',
        'reason',
        'vehicle_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
