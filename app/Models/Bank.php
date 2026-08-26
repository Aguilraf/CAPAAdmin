<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'account_number',
        'account_name',
        'currency',
        'import_template',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function movements()
    {
        return $this->hasMany(BankMovement::class);
    }
}
