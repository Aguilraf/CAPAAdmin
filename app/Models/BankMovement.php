<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_id',
        'operation_date',
        'application_date',
        'movement_number',
        'reference',
        'transaction_type',
        'description',
        'credit_amount',
        'debit_amount',
        'balance',
        'source_file',
        'fingerprint',
        'source_data',
    ];

    protected $casts = [
        'operation_date' => 'date:Y-m-d',
        'application_date' => 'date:Y-m-d',
        'credit_amount' => 'decimal:2',
        'debit_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'source_data' => 'array',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
