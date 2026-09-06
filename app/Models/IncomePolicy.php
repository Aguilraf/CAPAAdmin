<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_number',
        'policy_type',
        'account',
        'concept',
        'amount',
        'iva_amount',
        'start_date',
        'end_date',
        'observations',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'iva_amount' => 'decimal:2',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    public function details()
    {
        return $this->hasMany(IncomePolicyDetail::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function bankMovements()
    {
        return $this->hasMany(BankMovement::class);
    }
}
