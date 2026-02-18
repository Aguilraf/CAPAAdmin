<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class OrganismoScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Si el usuario es administrador, puede ver todo (no aplicamos filtro)
            // Asumimos que el rol se llama 'Administrador'
            if ($user->hasRole('Administrador')) {
                return;
            }

            // Si no es admin, filtrar por su organismo_id
            if ($user->organismo_id) {
                $builder->where($model->getTable() . '.organismo_id', $user->organismo_id);
            } else {
                // Si no tiene organismo asignado (caso raro), quizás no debería ver nada o ver todo? 
                // Por seguridad, si no es admin y no tiene organismo, no ve nada.
                // Ojo: Esto podría bloquear usuarios nuevos sin organismo.
                // Asignaremos organismo_id = 1 por defecto al crear usuarios si no se especifica.
                $builder->whereNull($model->getTable() . '.id'); // Return nothing
            }
        }
    }
}
