<?php

/**
 * Anwendungskonfiguration des ALS Panels
 *
 * Diese Datei konfiguriert die Laravel-Anwendung:
 * - Routing: Welche Routen-Dateien geladen werden
 * - Middleware: Alias-Definitionen fuer eigene Middlewares
 * - Exceptions: Globale Fehlerbehandlung
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))

    /*
    |--------------------------------------------------------------------------
    | Routing-Konfiguration
    |--------------------------------------------------------------------------
    | Legt fest, welche Routen-Dateien die Anwendung verwendet:
    | - web.php: Alle Web-Routen mit Session und CSRF-Schutz
    | - console.php: Artisan-Konsolenbefehle
    | - /up: Health-Check-Endpunkt fuer Server-Monitoring
    */
    ->withRouting(
        web:      __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health:   '/up',
    )

    /*
    |--------------------------------------------------------------------------
    | Middleware-Konfiguration
    |--------------------------------------------------------------------------
    | Registriert eigene Middleware-Aliase, die in den Routen verwendet werden:
    |
    | 'admin'       -> CheckAdmin: Prueft ob der Benutzer die Rolle 'admin' hat
    | 'mitarbeiter' -> CheckMitarbeiter: Prueft ob der Benutzer Mitarbeiter ist
    |
    | Verwendung in Routen: ->middleware(['auth', 'admin'])
    */
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'       => \App\Http\Middleware\CheckAdmin::class,
            'mitarbeiter' => \App\Http\Middleware\CheckMitarbeiter::class,
        ]);
    })

    /*
    |--------------------------------------------------------------------------
    | Globale Fehlerbehandlung
    |--------------------------------------------------------------------------
    | Hier koennen eigene Exception-Handler fuer spezifische Fehlertypen
    | registriert werden, z.B. 404 oder 403 Fehlerseiten.
    */
    ->withExceptions(function (Exceptions $exceptions): void {
        // Zukuenftige Fehlerbehandlung hier einfuegen
    })->create();
