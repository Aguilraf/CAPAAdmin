<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelExpenseRate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'role_level',
        'concept',
        'zone_i_limit',
        'zone_ii_limit',
        'effective_date',
    ];

    protected $casts = [
        'zone_i_limit' => 'decimal:2',
        'zone_ii_limit' => 'decimal:2',
        'effective_date' => 'date',
    ];
}
