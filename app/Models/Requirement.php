<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Requirement extends Model
{
    use SoftDeletes;

    const TYPES = [
        'bomberos' => 'Fondo de Bomberos',
        'revolvente' => 'Fondo Revolvente',
        'cfe' => 'CFE',
        'estandard' => 'Requerimiento Estándar',
        'viaticos' => 'Viáticos',
    ];

    protected $fillable = [
        'year',
        'requirement_number',
        'type',
        'assignment_date',
        'oficio_number',
        'coordinator_id',
        'director_id',
        'manager_id',
        'elaborator_id',
        'month_charged',
        'year_charged',
        'month_billed',
        'year_billed',
        'start_date',
        'end_date',
        'due_date',
        'description',
        'subtotal',
        'iva',
        'isr',
        'total',
        'status',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'isr' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(RequirementItem::class);
    }

    public function cfeReceipts()
    {
        return $this->hasMany(CfeReceipt::class);
    }

    public function coordinator()
    {
        return $this->belongsTo(Empleado::class, 'coordinator_id');
    }

    public function director()
    {
        return $this->belongsTo(Empleado::class, 'director_id');
    }

    public function manager()
    {
        return $this->belongsTo(Empleado::class, 'manager_id');
    }

    public function elaborator()
    {
        return $this->belongsTo(Empleado::class, 'elaborator_id');
    }

    public function getFormattedNumberAttribute()
    {
        return str_pad($this->requirement_number, 3, '0', STR_PAD_LEFT) . '/' . $this->year;
    }

    public function travelAllowance()
    {
        return $this->hasOne(TravelAllowance::class);
    }

    public static function getNumberingGroup($type)
    {
        return match ($type) {
            'bomberos' => 'bomberos',
            'revolvente' => 'fondo_fijo',
            default => 'standard',
        };
    }

    public static function getTypesByGroup($group)
    {
        return match ($group) {
            'bomberos' => ['bomberos'],
            'fondo_fijo' => ['revolvente'],
            'standard' => ['cfe', 'estandard', 'viaticos'],
            default => [],
        };
    }
}
