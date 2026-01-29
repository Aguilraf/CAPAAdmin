<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property int $community_id
 * @property bool $active
 * @property string|null $contact_number
 * @property string|null $credential_photo_path
 * @property string|null $geolocation
 */
class Firefighter extends Model
{
    protected $fillable = ['name', 'community_id', 'active', 'contact_number', 'credential_photo_path', 'geolocation', 'previous_firefighter', 'change_date', 'max_rounding_amount'];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function captures()
    {
        return $this->hasMany(Capture::class);
    }
}
