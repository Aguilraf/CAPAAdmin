<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Capture extends Model
{
    const REQUIREMENT_TYPES = [
        'bomberos' => 'Bomberos',
        'servicios' => 'Servicios',
        'obras' => 'Obras Públicas',
    ];

    protected $fillable = [
        'date',
        'year',
        'community_id',
        'firefighter_id',
        'subtotal',
        'commission',
        'total',
        'rounding_commission',
        'rounding_total',
        'requirement_number',
        'requirement_type', // Nuevo
        'assignment_date',
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function firefighter()
    {
        return $this->belongsTo(Firefighter::class);
    }

    /**
     * Get full requirement number with prefix
     * Example: B-2025-014
     */
    public function getFullRequirementNumber()
    {
        if (!$this->requirement_number) {
            return null;
        }

        $prefix = match ($this->requirement_type) {
            'bomberos' => 'B',
            'servicios' => 'S',
            'obras' => 'O',
            default => 'X',
        };

        return "{$prefix}-{$this->year}-{$this->requirement_number}";
    }
}
