<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            // Permitir que Laravel maneje errores de validación y autenticación por defecto
            if ($e instanceof ValidationException || $e instanceof AuthenticationException) {
                return null;
            }

            // Si es una petición Inertia o web estándar, evitar la "pantalla técnica"
            if ($request->isMethod('GET') || $request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('DELETE')) {
                if ($request->wantsJson() && !$request->inertia()) {
                    return null; // Deja que API responda JSON
                }

                // Loguear el error para soporte técnico (interno)
                // \Log::error($e); 
    
                // Redirigir atrás con mensaje amigable
                // Usamos 'error' como clave flash que el frontend ya sabe mostrar (ver Vacations/Admin/Index.jsx)
                return redirect()->back()->with('error', 'Ocurrió un error inesperado al procesar su solicitud. Por favor, contacte a soporte técnico.');
            }

            return null;
        });
    })->create();
