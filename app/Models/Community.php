<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    use \App\Traits\HasOrganismo;
    protected $fillable = ['name', 'geolocation', 'location_image_path', 'percentage', 'organismo_id'];

    public function firefighters()
    {
        return $this->hasMany(Firefighter::class);
    }

    public function captures()
    {
        return $this->hasMany(Capture::class);
    }
}
