<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organismo_id',
        'inventory_number',
        'unit_type',
        'brand',
        'vehicle_type',
        'color',
        'model_year',
        'serial_number',
        'engine_number',
        'invoice_number',
        'supplier',
        'policy_number',
        'area',
        'location',
        'sub_location',
        'custodian',
        'plate_number',
        'photo_path',
        'active',
    ];

    public function organismo()
    {
        return $this->belongsTo(\App\Models\Organismo::class);
    }

    protected $casts = [
        'active' => 'boolean',
    ];
}
