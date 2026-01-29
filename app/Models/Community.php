<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    protected $fillable = ['name', 'geolocation'];

    public function firefighters()
    {
        return $this->hasMany(Firefighter::class);
    }

    public function captures()
    {
        return $this->hasMany(Capture::class);
    }
}
