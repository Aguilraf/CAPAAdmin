<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyIncome extends Model
{
    use HasFactory;

    protected $fillable = [
        'income_date',
        'total_amount',
        'total_movements',
        'draef_amount',
        'draef_subtotal',
        'draef_iva',
    ];

    public function details()
    {
        return $this->hasMany(DailyIncomeDetail::class);
    }
}

