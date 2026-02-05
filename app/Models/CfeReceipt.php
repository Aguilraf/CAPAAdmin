<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CfeReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'requirement_id',
        'uuid',
        'rpu',
        'description',
        'period_start',
        'period_end',
        'subtotal',
        'iva',
        'rounding',
        'total',
    ];

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }
}
