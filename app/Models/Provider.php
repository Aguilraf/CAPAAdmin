<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'rfc',
        'bank_name',
        'account_number',
        'clabe',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
