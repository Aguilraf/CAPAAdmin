<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeAccount extends Model
{
    use HasFactory;

    protected $fillable = ['budget_account', 'accounting_account', 'concept', 'visible'];

    protected $casts = ['visible' => 'boolean'];
}
