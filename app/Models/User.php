<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'empleado_id',
        'organismo_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (\Illuminate\Support\Facades\Auth::check() && !$model->organismo_id) {
                $model->organismo_id = \Illuminate\Support\Facades\Auth::user()->organismo_id;
            }
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the empleado associated with the user.
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
    public function defaultMaterials()
    {
        return $this->belongsToMany(Material::class, 'user_material_defaults')
            ->withPivot('cantidad')
            ->withTimestamps();
    }
}
