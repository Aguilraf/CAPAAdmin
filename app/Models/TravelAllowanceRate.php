<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelAllowanceRate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'partida_id',
        'cargo',
        'nivel',
        'zona_1_amount',
        'zona_2_amount',
        'rate_type',
        'year',
        'active',
    ];

    protected $casts = [
        'zona_1_amount' => 'decimal:2',
        'zona_2_amount' => 'decimal:2',
        'year' => 'integer',
        'active' => 'boolean',
    ];

    // Relationships
    public function partida()
    {
        return $this->belongsTo(Partida::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeForEmployee($query, $cargo, $nivel)
    {
        return $query->where('cargo', $cargo)->where('nivel', $nivel);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('rate_type', $type);
    }
}
