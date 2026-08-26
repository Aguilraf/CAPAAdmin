<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomePolicyDetail extends Model
{
    use HasFactory;

    protected $fillable = ['income_policy_id', 'income_account_id', 'amount'];

    protected $casts = ['amount' => 'decimal:2'];

    public function account()
    {
        return $this->belongsTo(IncomeAccount::class, 'income_account_id');
    }
}
