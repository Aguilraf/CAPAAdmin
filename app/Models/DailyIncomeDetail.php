<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyIncomeDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_income_id',
        'bank_movement_id',
    ];

    public function movement()
    {
        return $this->belongsTo(BankMovement::class, 'bank_movement_id');
    }
}

