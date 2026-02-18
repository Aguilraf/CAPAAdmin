<?php

namespace App\Traits;

use App\Models\Organismo;
use App\Scopes\OrganismoScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait HasOrganismo
{
    /**
     * Boot the trait.
     */
    protected static function bootHasOrganismo()
    {
        static::addGlobalScope(new OrganismoScope);

        static::creating(function ($model) {
            if (Auth::check() && !$model->organismo_id) {
                $model->organismo_id = Auth::user()->organismo_id;
            }
        });
    }

    /**
     * Get the organismo that owns the model.
     */
    public function organismo(): BelongsTo
    {
        return $this->belongsTo(Organismo::class);
    }
}
