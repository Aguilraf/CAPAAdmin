<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        \Illuminate\Validation\Rules\Password::defaults(function () {
            return \Illuminate\Validation\Rules\Password::min(4);
        });

        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'SaldoVacaciones' => \App\Models\SaldoVacaciones::class,
        ]);

        // Register Backup Observers
        \App\Models\Firefighter::observe(\App\Observers\BackupObserver::class);
        \App\Models\Community::observe(\App\Observers\BackupObserver::class);
    }
}
