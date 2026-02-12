<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inventory_number',
        'unit_number',
        'brand',
        'type',
        'color',
        'model',
        'serial_number',
        'motor_number',
        'assignee_area',
        'plate',
        'resguardante',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
